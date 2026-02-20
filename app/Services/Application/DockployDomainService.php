<?php

namespace App\Services\Application;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DockployDomainService
{
    public function isConfigured(): bool
    {
        $token = config('dockploy.token');
        $appId = config('dockploy.application_id');

        return ! empty($token) && ! empty($appId);
    }

    /**
     * List domains for the configured application.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listDomains(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $url = rtrim(config('dockploy.api_url'), '/').'/api/domain.byApplicationId';
        $appId = config('dockploy.application_id');
        $token = config('dockploy.token');

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'x-api-key' => $token,
            ])->get($url, [
                'applicationId' => $appId,
            ]);

            if (! $response->successful()) {
                Log::warning('Dockploy list domains failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $data = $response->json();
            if (! is_array($data)) {
                return [];
            }

            return $data;
        } catch (\Throwable $e) {
            Log::warning('Dockploy list domains error', ['message' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Check if a host (subdomain) already exists for this application.
     */
    public function domainExists(string $host): bool
    {
        $domains = $this->listDomains();
        foreach ($domains as $domain) {
            if (isset($domain['host']) && strtolower((string) $domain['host']) === strtolower($host)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a subdomain for the application (e.g. institution-slug.talenttune.site).
     * Routes traffic to the same app on port 80 with HTTPS and Let's Encrypt.
     *
     * @return array{success: bool, domain_id?: string, message?: string}
     */
    public function createSubdomain(string $host): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'message' => 'Dockploy not configured'];
        }

        $baseUrl = rtrim(config('dockploy.api_url'), '/');
        $url = $baseUrl.'/api/domain.create';
        $token = config('dockploy.token');
        $appId = config('dockploy.application_id');

        if ($this->domainExists($host)) {
            Log::info('Dockploy domain already exists', ['host' => $host]);

            return ['success' => true, 'message' => 'Domain already exists'];
        }

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
                'x-api-key' => $token,
            ])->post($url, [
                'host' => $host,
                'path' => '/',
                'port' => 80,
                'https' => true,
                'applicationId' => $appId,
                'certificateType' => 'letsencrypt',
                'domainType' => 'application',
                'internalPath' => '/',
                'stripPath' => false,
            ]);

            if (! $response->successful()) {
                Log::warning('Dockploy create domain failed', [
                    'host' => $host,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'message' => $response->body() ?: 'Create domain request failed',
                ];
            }

            $data = $response->json();
            $domainId = $data['domainId'] ?? $data['domain_id'] ?? null;

            Log::info('Dockploy subdomain created', ['host' => $host, 'domainId' => $domainId]);

            return [
                'success' => true,
                'domain_id' => $domainId,
            ];
        } catch (\Throwable $e) {
            Log::warning('Dockploy create domain error', [
                'host' => $host,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
