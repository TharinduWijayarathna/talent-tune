<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Notifications\AdminCredentialsSent;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

class AdminAdminService
{
    /**
     * Get all TalentTune admins (role = admin).
     */
    public function getAdmins(): array
    {
        return User::where('role', 'admin')
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'email_verified_at' => $u->email_verified_at?->toISOString(),
                'created_at' => $u->created_at->toISOString(),
            ])
            ->all();
    }

    /**
     * Create a TalentTune admin and send credentials email.
     */
    public function createAdmin(array $validated): User
    {
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'institution_id' => null,
        ]);

        $loginUrl = rtrim(config('app.url'), '/').'/login';

        Notification::route('mail', $user->email)
            ->notify(new AdminCredentialsSent(
                $user->name,
                $user->email,
                $validated['password'],
                $loginUrl,
                parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https'
            ));

        return $user;
    }
}
