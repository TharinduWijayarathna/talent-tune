<?php

use App\Models\Institution;
use App\Services\Application\InstitutionResolverService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

beforeEach(function () {
    $this->service = new InstitutionResolverService;
});

test('extractSubdomain returns first part for host with three or more parts', function () {
    expect($this->service->extractSubdomain('acme.example.com'))->toBe('acme');
    expect($this->service->extractSubdomain('myschool.talenttune.test'))->toBe('myschool');
});

test('extractSubdomain returns first part for two-part .test host', function () {
    expect($this->service->extractSubdomain('acme.test'))->toBe('acme');
});

test('extractSubdomain returns null for two-part non-test host', function () {
    expect($this->service->extractSubdomain('example.com'))->toBeNull();
});

test('extractSubdomain returns null for single part host', function () {
    expect($this->service->extractSubdomain('localhost'))->toBeNull();
});

test('resolve returns institution from request attributes when set', function () {
    $institution = Institution::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'email' => 'acme@example.com',
        'is_active' => true,
    ]);
    $request = Request::createFromBase(
        SymfonyRequest::create('https://acme.example.com/', 'GET')
    );
    $request->attributes->set('institution', $institution);

    $result = $this->service->resolve($request);

    expect($result)->toBe($institution);
});

test('resolve returns institution by slug from subdomain when not in attributes', function () {
    $institution = Institution::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'email' => 'acme@example.com',
        'is_active' => true,
    ]);
    $request = Request::createFromBase(
        SymfonyRequest::create('https://acme.example.com/', 'GET')
    );

    $result = $this->service->resolve($request);

    expect($result)->not->toBeNull();
    expect($result->id)->toBe($institution->id);
});

test('resolve returns null for www subdomain', function () {
    $request = Request::createFromBase(
        SymfonyRequest::create('https://www.example.com/', 'GET')
    );

    expect($this->service->resolve($request))->toBeNull();
});

test('resolveActive returns only active institution', function () {
    $active = Institution::create([
        'name' => 'Active',
        'slug' => 'active',
        'email' => 'active@example.com',
        'is_active' => true,
    ]);
    $inactive = Institution::create([
        'name' => 'Inactive',
        'slug' => 'inactive',
        'email' => 'inactive@example.com',
        'is_active' => false,
    ]);

    $requestActive = Request::createFromBase(
        SymfonyRequest::create('https://active.example.com/', 'GET')
    );
    $requestInactive = Request::createFromBase(
        SymfonyRequest::create('https://inactive.example.com/', 'GET')
    );

    expect($this->service->resolveActive($requestActive)->id)->toBe($active->id);
    expect($this->service->resolveActive($requestInactive))->toBeNull();
});
