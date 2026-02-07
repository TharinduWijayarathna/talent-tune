<?php

use App\Models\Institution;
use App\Models\User;
use App\Models\Viva;
use App\Models\VivaStudentSubmission;
use App\Services\Ai\RubricService;
use App\Services\Application\StudentService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->rubricService = \Mockery::mock(RubricService::class);
    $this->service = new StudentService($this->rubricService);
    $this->institution = Institution::create([
        'name' => 'Test School',
        'slug' => 'test-school',
        'email' => 'admin@testschool.edu',
        'is_active' => true,
    ]);
});

afterEach(function () {
    \Mockery::close();
});

test('getDashboardData returns stats and upcoming vivas for student with batch', function () {
    $lecturer = User::create([
        'name' => 'Dr. Lecturer',
        'email' => 'lecturer@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);
    $student = User::create([
        'name' => 'Student',
        'email' => 'student@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => '2024',
    ]);
    Viva::create([
        'institution_id' => $this->institution->id,
        'lecturer_id' => $lecturer->id,
        'title' => 'Viva 1',
        'batch' => '2024',
        'scheduled_at' => now()->addDay(),
        'status' => 'upcoming',
    ]);

    $result = $this->service->getDashboardData($this->institution, $student);

    expect($result)->toHaveKeys(['stats', 'upcomingVivas']);
    expect($result['stats']['upcomingVivas'])->toBe(1);
    expect($result['stats']['totalSessions'])->toBe(1);
    expect($result['upcomingVivas'])->toHaveCount(1);
    expect($result['upcomingVivas'][0]['title'])->toBe('Viva 1');
});

test('getDashboardData returns zeros when student has no batch', function () {
    $student = User::create([
        'name' => 'No Batch',
        'email' => 'nobatch@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => null,
    ]);

    $result = $this->service->getDashboardData($this->institution, $student);

    expect($result['stats']['upcomingVivas'])->toBe(0);
    expect($result['stats']['totalSessions'])->toBe(0);
    expect($result['upcomingVivas'])->toBeArray();
});

test('getVivaSessions returns empty when user has no batch', function () {
    $student = User::create([
        'name' => 'No Batch',
        'email' => 'nobatch@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => null,
    ]);

    $result = $this->service->getVivaSessions($this->institution, $student);

    expect($result)->toBe([]);
});

test('getVivaSessions returns vivas for student batch with can_attend and marks', function () {
    $lecturer = User::create([
        'name' => 'Dr. Lecturer',
        'email' => 'lecturer@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);
    $student = User::create([
        'name' => 'Student',
        'email' => 'student@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => '2024',
    ]);
    $viva = Viva::create([
        'institution_id' => $this->institution->id,
        'lecturer_id' => $lecturer->id,
        'title' => 'Past Viva',
        'batch' => '2024',
        'scheduled_at' => now()->subHour(),
        'status' => 'upcoming',
    ]);

    $result = $this->service->getVivaSessions($this->institution, $student);

    expect($result)->toHaveCount(1);
    expect($result[0])->toHaveKeys(['id', 'title', 'status', 'can_attend', 'marks']);
    expect($result[0]['title'])->toBe('Past Viva');
});

test('completeVivaSubmission saves answers and uses rubric score when available', function () {
    $lecturer = User::create([
        'name' => 'Dr. Lecturer',
        'email' => 'lecturer@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'lecturer',
        'institution_id' => $this->institution->id,
    ]);
    $student = User::create([
        'name' => 'Student',
        'email' => 'student@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => '2024',
    ]);
    $viva = Viva::create([
        'institution_id' => $this->institution->id,
        'lecturer_id' => $lecturer->id,
        'title' => 'Viva',
        'batch' => '2024',
        'scheduled_at' => now(),
        'status' => 'upcoming',
    ]);
    $submission = VivaStudentSubmission::create([
        'viva_id' => $viva->id,
        'student_id' => $student->id,
        'status' => 'in_progress',
    ]);

    $this->rubricService->shouldReceive('getRubricScore')
        ->once()
        ->with([5, 6, 7, 8, 9])
        ->andReturn(['success' => true, 'score' => 72.5]);

    $answers = [
        ['score_1_10' => 5],
        ['score_1_10' => 6],
        ['score_1_10' => 7],
        ['score_1_10' => 8],
        ['score_1_10' => 9],
    ];
    $validated = ['submission_id' => $submission->id, 'answers' => $answers];

    $result = $this->service->completeVivaSubmission($student, $validated);

    expect($result['success'])->toBeTrue();
    expect($result['rubric_from_service'])->toBeTrue();
    $submission->refresh();
    expect($submission->status)->toBe('completed');
    expect($submission->total_score)->toBe(73); // rounded
});

test('uploadVivaDocument stores file and updates submission', function () {
    Storage::fake('private');
    $student = User::create([
        'name' => 'Student',
        'email' => 'student@testschool.edu',
        'password' => bcrypt('password'),
        'role' => 'student',
        'institution_id' => $this->institution->id,
        'batch' => '2024',
    ]);
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
        'title' => 'Viva',
        'batch' => '2024',
        'scheduled_at' => now(),
        'status' => 'upcoming',
    ]);
    $file = UploadedFile::fake()->create('document.pdf', 100);

    $result = $this->service->uploadVivaDocument($this->institution, $student, $viva->id, $file);

    expect($result['success'])->toBeTrue();
    expect($result['document_path'])->toContain('vivas/student-documents');
    $submission = VivaStudentSubmission::where('viva_id', $viva->id)->where('student_id', $student->id)->first();
    expect($submission->document_path)->toBe($result['document_path']);
    expect($submission->status)->toBe('in_progress');
});
