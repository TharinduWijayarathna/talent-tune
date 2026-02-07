<?php

use App\Models\Institution;
use App\Services\Application\InstitutionService;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->service = new InstitutionService;
});

test('create creates institution with generated slug', function () {
    $validated = [
        'name' => 'Test University',
        'email' => 'contact@test.edu',
        'contact_person' => 'John Doe',
        'phone' => '+1234567890',
        'address' => '123 Main St',
        'primary_color' => '#3b82f6',
    ];

    $institution = $this->service->create($validated);

    expect($institution)->toBeInstanceOf(Institution::class);
    expect($institution->name)->toBe('Test University');
    expect($institution->email)->toBe('contact@test.edu');
    expect($institution->contact_person)->toBe('John Doe');
    expect($institution->is_active)->toBeFalse();
    expect($institution->slug)->not->toBeEmpty();
    expect($institution->slug)->toMatch('/^test-university(-[0-9]+)?$/');
});

test('generateUniqueSlug returns unique slug when name exists', function () {
    Institution::create([
        'name' => 'Duplicate',
        'slug' => 'duplicate',
        'email' => 'first@example.com',
        'is_active' => false,
    ]);

    $slug1 = $this->service->generateUniqueSlug('Duplicate');
    expect($slug1)->toBe('duplicate-1');

    Institution::create([
        'name' => 'Duplicate 2',
        'slug' => 'duplicate-1',
        'email' => 'second@example.com',
        'is_active' => false,
    ]);

    $slug2 = $this->service->generateUniqueSlug('Duplicate');
    expect($slug2)->toBe('duplicate-2');
});

test('getListForAdmin returns all institutions with expected shape', function () {
    Institution::create([
        'name' => 'First',
        'slug' => 'first',
        'email' => 'first@example.com',
        'is_active' => true,
    ]);

    $list = $this->service->getListForAdmin();

    expect($list)->toBeArray();
    expect($list)->toHaveCount(1);
    expect($list[0])->toHaveKeys(['id', 'name', 'slug', 'email', 'contact_person', 'phone', 'address', 'is_active', 'created_at']);
    expect($list[0]['name'])->toBe('First');
    expect($list[0]['slug'])->toBe('first');
});

test('updateStatus updates is_active and does not send activation when deactivating', function () {
    $institution = Institution::create([
        'name' => 'Active',
        'slug' => 'active',
        'email' => 'active@example.com',
        'is_active' => true,
    ]);
    $request = Request::create('https://active.example.com/', 'GET');

    $this->service->updateStatus($institution, false, $request);

    expect($institution->fresh()->is_active)->toBeFalse();
});
