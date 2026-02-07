<?php

use App\Models\Institution;
use App\Models\User;
use App\Services\Application\InstitutionUserService;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->service = new InstitutionUserService;
    $this->institution = Institution::create([
        'name' => 'Test School',
        'slug' => 'test-school',
        'email' => 'admin@testschool.edu',
        'is_active' => true,
    ]);
});

test('getLecturers returns empty array when no lecturers', function () {
    $result = $this->service->getLecturers($this->institution);

    expect($result)->toBeArray();
    expect($result)->toHaveCount(0);
});

test('getLecturers returns lecturers for institution', function () {
    User::create([
        'name' => 'Dr. Smith',
        'email' => 'smith@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
        'employee_id' => 'EMP001',
        'department' => 'CS',
    ]);

    $result = $this->service->getLecturers($this->institution);

    expect($result)->toHaveCount(1);
    expect($result[0]['name'])->toBe('Dr. Smith');
    expect($result[0]['email'])->toBe('smith@testschool.edu');
    expect($result[0])->not->toHaveKey('role');
    expect($result[0])->toHaveKeys(['id', 'name', 'email', 'employee_id', 'department', 'status', 'totalSessions', 'created_at']);
});

test('getStudents returns students for institution', function () {
    User::create([
        'name' => 'Jane Student',
        'email' => 'jane@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'student_id' => 'STU001',
        'batch' => '2024',
        'department' => 'CS',
    ]);

    $result = $this->service->getStudents($this->institution);

    expect($result)->toHaveCount(1);
    expect($result[0]['name'])->toBe('Jane Student');
    expect($result[0]['batch'])->toBe('2024');
    expect($result[0])->toHaveKeys(['id', 'name', 'email', 'student_id', 'batch', 'department', 'status', 'completedVivas', 'created_at']);
});

test('createLecturer creates user and optionally sends notification', function () {
    Notification::fake();

    $validated = [
        'name' => 'New Lecturer',
        'email' => 'newlecturer@testschool.edu',
        'password' => 'secret123',
        'department' => 'Math',
        'employee_id' => 'EMP002',
    ];

    $user = $this->service->createLecturer($this->institution, $validated, 'example.com', 'https');

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->toBe('New Lecturer');
    expect($user->email)->toBe('newlecturer@testschool.edu');
    expect($user->role)->toBe('lecturer');
    expect($user->institution_id)->toBe($this->institution->id);
    expect($user->department)->toBe('Math');
    expect($user->employee_id)->toBe('EMP002');

    Notification::assertCount(1);
});

test('createStudent creates user without notification when baseDomain is null', function () {
    Notification::fake();

    $validated = [
        'name' => 'New Student',
        'email' => 'newstudent@testschool.edu',
        'password' => 'secret456',
        'batch' => '2025',
        'student_id' => 'STU002',
    ];

    $user = $this->service->createStudent($this->institution, $validated, null);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->role)->toBe('student');
    expect($user->batch)->toBe('2025');

    Notification::assertCount(0);
});

test('getLecturerForEdit returns lecturer or fails', function () {
    $lecturer = User::create([
        'name' => 'Edit Me',
        'email' => 'edit@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);

    $found = $this->service->getLecturerForEdit($this->institution, $lecturer->id);
    expect($found->id)->toBe($lecturer->id);

    $this->service->getLecturerForEdit($this->institution, 99999);
})->throws(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

test('updateLecturer and updateStudent update user', function () {
    $lecturer = User::create([
        'name' => 'Original',
        'email' => 'orig@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);

    $this->service->updateLecturer($lecturer, ['name' => 'Updated Name', 'department' => 'Physics']);
    $lecturer->refresh();
    expect($lecturer->name)->toBe('Updated Name');
    expect($lecturer->department)->toBe('Physics');
});

test('destroyLecturer and destroyStudent delete user', function () {
    $lecturer = User::create([
        'name' => 'To Delete',
        'email' => 'delete@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);
    $id = $lecturer->id;

    $this->service->destroyLecturer($lecturer);

    expect(User::find($id))->toBeNull();
});
