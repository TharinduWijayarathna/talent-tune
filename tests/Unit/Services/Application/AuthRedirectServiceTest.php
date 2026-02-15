<?php

use App\Models\Institution;
use App\Models\User;
use App\Services\Application\AuthRedirectService;
use App\Services\Application\InstitutionResolverService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

beforeEach(function () {
    $this->resolver = new InstitutionResolverService;
    $this->service = new AuthRedirectService($this->resolver);
});

test('getRedirectUrl returns admin dashboard for admin user', function () {
    $user = User::factory()->make(['role' => 'admin']);
    $request = Request::createFromBase(SymfonyRequest::create('https://example.com/', 'GET'));

    $url = $this->service->getRedirectUrl($user, $request);

    expect($url)->toBe('/admin/dashboard');
});

test('getRedirectUrl returns slash when user has no institution', function () {
    $user = User::factory()->make(['role' => 'institution', 'institution_id' => null]);
    $request = Request::createFromBase(SymfonyRequest::create('https://example.com/', 'GET'));

    $url = $this->service->getRedirectUrl($user, $request);

    expect($url)->toBe('/');
});

test('getRedirectUrl returns slash when institution is inactive', function () {
    $institution = Institution::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'email' => 'acme@example.com',
        'is_active' => false,
    ]);
    $user = User::factory()->make(['role' => 'institution', 'institution_id' => $institution->id]);
    $user->setRelation('institution', $institution);
    $request = Request::createFromBase(SymfonyRequest::create('https://other.example.com/', 'GET'));

    $url = $this->service->getRedirectUrl($user, $request);

    expect($url)->toBe('/');
});

test('getRedirectUrl returns slash when already on institution subdomain', function () {
    $institution = Institution::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'email' => 'acme@example.com',
        'is_active' => true,
        'subscription_status' => 'active',
    ]);
    $user = User::factory()->make(['role' => 'student', 'institution_id' => $institution->id]);
    $user->setRelation('institution', $institution);
    $request = Request::createFromBase(SymfonyRequest::create('https://acme.example.com/', 'GET'));

    $url = $this->service->getRedirectUrl($user, $request);

    expect($url)->toBe('/');
});

test('getRedirectUrl returns institution subdomain URL when on different host', function () {
    $institution = Institution::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'email' => 'acme@example.com',
        'is_active' => true,
        'subscription_status' => 'active',
    ]);
    $user = User::factory()->make(['role' => 'student', 'institution_id' => $institution->id]);
    $user->setRelation('institution', $institution);
    $request = Request::createFromBase(SymfonyRequest::create('https://www.example.com/', 'GET'));

    $url = $this->service->getRedirectUrl($user, $request);

    expect($url)->toBe('https://acme.example.com/');
});

test('getBaseDomain returns last two parts for standard host', function () {
    expect($this->service->getBaseDomain('acme.example.com'))->toBe('example.com');
});

test('getBaseDomain returns full host for .test with two parts', function () {
    expect($this->service->getBaseDomain('acme.test'))->toBe('acme.test');
});

test('getBaseDomain returns last two parts for .test with three parts', function () {
    expect($this->service->getBaseDomain('app.talenttune.test'))->toBe('talenttune.test');
});

test('getBaseDomainFromRequest uses config domain when set', function () {
    config(['domain.domain' => 'myapp.com']);
    $request = Request::createFromBase(SymfonyRequest::create('https://other.example.com/', 'GET'));

    $baseDomain = $this->service->getBaseDomainFromRequest($request);

    expect($baseDomain)->toBe('myapp.com');
});
