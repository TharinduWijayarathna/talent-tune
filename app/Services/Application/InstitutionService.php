<?php

namespace App\Services\Application;

use App\Models\Institution;
use App\Models\User;
use App\Notifications\InstitutionActivated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class InstitutionService
{
    public function __construct(
        protected DockployDomainService $dockployDomainService
    ) {}

    public function create(array $validated): Institution
    {
        $slug = $this->generateUniqueSlug($validated['name']);

        $institution = Institution::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'email' => $validated['email'],
            'contact_person' => $validated['contact_person'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'primary_color' => $validated['primary_color'] ?? '#3b82f6',
            'settings' => [
                'email' => $validated['email'],
                'contact_person' => $validated['contact_person'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ],
            'is_active' => false,
        ]);

        $this->createSubdomainIfConfigured($institution);

        return $institution;
    }

    public function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $baseSlug = $slug;
        $counter = 1;

        while (Institution::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Update institution details (admin only). Slug is not changed.
     */
    public function update(Institution $institution, array $data): void
    {
        $institution->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'contact_person' => $data['contact_person'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'primary_color' => $data['primary_color'] ?? $institution->primary_color,
            'is_active' => $data['is_active'] ?? $institution->is_active,
            'settings' => array_merge($institution->settings ?? [], [
                'email' => $data['email'],
                'contact_person' => $data['contact_person'],
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]),
        ]);
    }

    public function getListForAdmin(): array
    {
        return Institution::latest()->get()->map(function (Institution $institution) {
            return [
                'id' => $institution->id,
                'name' => $institution->name,
                'slug' => $institution->slug,
                'email' => $institution->email ?? ($institution->settings['email'] ?? null),
                'contact_person' => $institution->contact_person ?? ($institution->settings['contact_person'] ?? null),
                'phone' => $institution->phone ?? ($institution->settings['phone'] ?? null),
                'address' => $institution->address ?? ($institution->settings['address'] ?? null),
                'is_active' => $institution->is_active,
                'subscription_status' => $institution->subscription_status,
                'created_at' => $institution->created_at->toISOString(),
            ];
        })->all();
    }

    public function updateStatus(Institution $institution, bool $isActive, Request $request): void
    {
        $wasInactive = ! $institution->is_active;
        $isActivating = $wasInactive && $isActive;

        $institution->update(['is_active' => $isActive]);

        if ($isActivating) {
            $this->activateInstitution($institution, $request);
        }
    }

    public function activateInstitution(Institution $institution, Request $request): void
    {
        $this->createSubdomainIfConfigured($institution);

        $existingAdmin = User::where('institution_id', $institution->id)
            ->where('role', 'institution')
            ->first();

        if ($existingAdmin) {
            $password = Str::random(12);
            $existingAdmin->update(['password' => Hash::make($password)]);
            $this->sendActivationEmail($institution, $existingAdmin->email, $password, $request);

            return;
        }

        $adminEmail = $institution->email ?? ($institution->settings['email'] ?? null);

        if (! $adminEmail) {
            Log::warning("Cannot activate institution {$institution->id}: No email address found");

            return;
        }

        $existingUser = User::where('email', $adminEmail)->first();

        if ($existingUser) {
            $password = Str::random(12);
            $existingUser->update([
                'institution_id' => $institution->id,
                'role' => 'institution',
                'password' => Hash::make($password),
            ]);
            $this->sendActivationEmail($institution, $adminEmail, $password, $request);

            return;
        }

        $password = Str::random(12);
        User::create([
            'name' => $institution->contact_person ?? $institution->name,
            'email' => $adminEmail,
            'password' => Hash::make($password),
            'role' => 'institution',
            'institution_id' => $institution->id,
            'department' => 'Administration',
            'email_verified_at' => now(),
        ]);

        $this->sendActivationEmail($institution, $adminEmail, $password, $request);
    }

    /**
     * Create institution subdomain (e.g. {slug}.talenttune.site) via Dockploy/Vintorr API in production.
     */
    protected function createSubdomainIfConfigured(Institution $institution): void
    {
        if (! $this->dockployDomainService->isConfigured()) {
            return;
        }

        $baseDomain = config('domain.domain');
        if (empty($baseDomain)) {
            Log::debug('Dockploy: APP_DOMAIN not set, skipping subdomain create');

            return;
        }

        $host = $institution->slug.'.'.$baseDomain;
        $result = $this->dockployDomainService->createSubdomain($host);

        if (! $result['success']) {
            Log::warning('Dockploy subdomain create failed for institution', [
                'institution_id' => $institution->id,
                'host' => $host,
                'message' => $result['message'] ?? 'Unknown error',
            ]);
        }
    }

    public function sendActivationEmail(Institution $institution, string $email, string $password, Request $request): void
    {
        $baseDomain = config('domain.domain');
        if ($baseDomain === null || $baseDomain === '') {
            $host = $request->getHost();
            $parts = explode('.', $host);
            $localTld = config('domain.local_tld', '.test');
            $baseDomain = ($localTld !== '' && str_ends_with($host, $localTld))
                ? (count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host)
                : (count($parts) >= 2 ? implode('.', array_slice($parts, -2)) : $host);
        }

        $paymentUrl = URL::temporarySignedRoute(
            'subscription.show',
            now()->addDays(7),
            ['institution' => $institution->slug]
        );

        Notification::route('mail', $email)
            ->notify(new InstitutionActivated($institution, $email, $password, $baseDomain, $paymentUrl));
    }
}
