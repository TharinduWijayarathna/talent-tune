<?php

use App\Models\Batch;
use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use App\Services\Application\LecturerService;

beforeEach(function () {
    $this->service = new LecturerService;
    $this->institution = Institution::create([
        'name' => 'Test School',
        'slug' => 'test-school',
        'email' => 'admin@testschool.edu',
        'is_active' => true,
    ]);
});

afterEach(function () {
});

test('getDashboardData returns stats and recent sessions for lecturer', function () {
    $lecturer = User::create([
        'name' => 'Dr. Lecturer',
        'email' => 'lecturer@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);
    Viva::create([
        'institution_id' => $this->institution->id,
        'lecturer_id' => $lecturer->id,
        'title' => 'Session 1',
        'batch' => '2024',
        'scheduled_at' => now(),
        'status' => 'upcoming',
    ]);
    Viva::create([
        'institution_id' => $this->institution->id,
        'lecturer_id' => $lecturer->id,
        'title' => 'Session 2',
        'batch' => '2024',
        'scheduled_at' => now()->subDay(),
        'status' => 'completed',
    ]);

    $result = $this->service->getDashboardData($this->institution, $lecturer);

    expect($result)->toHaveKeys(['stats', 'recentSessions']);
    expect($result['stats']['totalSessions'])->toBe(2);
    expect($result['stats']['activeSessions'])->toBe(1);
    expect($result['stats']['completedSessions'])->toBe(1);
    expect($result['recentSessions'])->toHaveCount(2);
});

test('getVivas returns vivas for lecturer', function () {
    $lecturer = User::create([
        'name' => 'Dr. Lecturer',
        'email' => 'lecturer@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);
    Viva::create([
        'institution_id' => $this->institution->id,
        'lecturer_id' => $lecturer->id,
        'title' => 'My Viva',
        'description' => 'Desc',
        'batch' => '2024',
        'scheduled_at' => now(),
        'status' => 'upcoming',
    ]);

    $result = $this->service->getVivas($this->institution, $lecturer);

    expect($result)->toHaveCount(1);
    expect($result[0]['title'])->toBe('My Viva');
    expect($result[0])->toHaveKeys(['id', 'title', 'description', 'batch', 'scheduled_at', 'status', 'students']);
});

test('getBatchesForInstitution returns batch names from Batch model first', function () {
    Batch::create(['institution_id' => $this->institution->id, 'name' => 'Batch A']);
    Batch::create(['institution_id' => $this->institution->id, 'name' => 'Batch B']);

    $result = $this->service->getBatchesForInstitution($this->institution);

    expect($result)->toBe(['Batch A', 'Batch B']);
});

test('getBatchesForInstitution falls back to distinct student batches when no Batch records', function () {
    User::create([
        'name' => 'S1',
        'email' => 's1@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => '2024',
    ]);
    User::create([
        'name' => 'S2',
        'email' => 's2@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => '2024',
    ]);
    User::create([
        'name' => 'S3',
        'email' => 's3@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => '2025',
    ]);

    $result = $this->service->getBatchesForInstitution($this->institution);

    expect($result)->toContain('2024');
    expect($result)->toContain('2025');
});

test('getVivaForShow returns viva for lecturer or fails', function () {
    $lecturer = User::create([
        'name' => 'Dr. Lecturer',
        'email' => 'lecturer@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);
    $viva = Viva::create([
        'institution_id' => $this->institution->id,
        'lecturer_id' => $lecturer->id,
        'title' => 'My Viva',
        'batch' => '2024',
        'scheduled_at' => now(),
        'status' => 'upcoming',
    ]);

    $found = $this->service->getVivaForShow($this->institution, $lecturer, $viva->id);
    expect($found->id)->toBe($viva->id);

    $this->service->getVivaForShow($this->institution, $lecturer, 99999);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

test('closeViva updates status to completed', function () {
    $lecturer = User::create([
        'name' => 'Dr. Lecturer',
        'email' => 'lecturer@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);
    $viva = Viva::create([
        'institution_id' => $this->institution->id,
        'lecturer_id' => $lecturer->id,
        'title' => 'To Close',
        'batch' => '2024',
        'scheduled_at' => now(),
        'status' => 'upcoming',
    ]);

    $updated = $this->service->closeViva($this->institution, $lecturer, $viva->id);

    expect($updated->status)->toBe('completed');
});

test('createViva creates viva with instructions and no base_prompt', function () {
    $lecturer = User::create([
        'name' => 'Dr. Lecturer',
        'email' => 'lecturer@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);

    $validated = [
        'title' => 'Viva Title',
        'description' => 'Description',
        'batch' => '2024',
        'date' => now()->addDay()->format('Y-m-d'),
        'time' => '14:00',
        'instructions' => 'Instructions',
    ];

    $viva = $this->service->createViva($this->institution, $lecturer, $validated);

    expect($viva)->toBeInstanceOf(Viva::class);
    expect($viva->title)->toBe('Viva Title');
    expect($viva->batch)->toBe('2024');
    expect($viva->instructions)->toBe('Instructions');
    expect($viva->viva_background)->toBeNull();
    expect($viva->base_prompt)->toBeNull();
    expect($viva->status)->toBe('upcoming');
});
