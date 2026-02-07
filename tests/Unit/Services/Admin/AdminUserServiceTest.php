<?php

use App\Models\Institution;
use App\Models\User;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->service = new AdminUserService;
});

test('getUsersWithFilters returns paginated users with expected shape', function () {
    $institution = Institution::create([
        'name' => 'School',
        'slug' => 'school',
        'email' => 'admin@school.edu',
        'is_active' => true,
    ]);
    User::create([
        'name' => 'Admin User',
        'email' => 'admin@school.edu',
        'password' => bcrypt('password'),
        'role' => 'institution',
        'institution_id' => $institution->id,
    ]);

    $request = Request::create('/', 'GET');
    $result = $this->service->getUsersWithFilters($request);

    expect($result)->toHaveKeys(['users', 'filters']);
    expect($result['users']->count())->toBeGreaterThan(0);
    $first = $result['users']->items()[0];
    expect($first)->toHaveKeys(['id', 'name', 'email', 'role', 'institution', 'created_at']);
});

test('getUsersWithFilters filters by role when provided', function () {
    $institution = Institution::create([
        'name' => 'School',
        'slug' => 'school',
        'email' => 'admin@school.edu',
        'is_active' => true,
    ]);
    User::create([
        'name' => 'Lecturer',
        'email' => 'lecturer@school.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $institution->id,
    ]);
    User::create([
        'name' => 'Student',
        'email' => 'student@school.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $institution->id,
    ]);

    $request = Request::create('/', 'GET', ['role' => 'lecturer']);
    $result = $this->service->getUsersWithFilters($request);

    foreach ($result['users'] as $user) {
        expect($user['role'])->toBe('lecturer');
    }
    expect($result['filters']['role'])->toBe('lecturer');
});

test('getUsersWithFilters filters by search when provided', function () {
    $institution = Institution::create([
        'name' => 'School',
        'slug' => 'school',
        'email' => 'admin@school.edu',
        'is_active' => true,
    ]);
    User::create([
        'name' => 'UniqueName Person',
        'email' => 'uniquename@school.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $institution->id,
    ]);

    $request = Request::create('/', 'GET', ['search' => 'UniqueName']);
    $result = $this->service->getUsersWithFilters($request);

    expect($result['users']->count())->toBeGreaterThan(0);
    expect($result['filters']['search'])->toBe('UniqueName');
});
