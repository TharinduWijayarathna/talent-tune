<?php

use App\Services\Ai\RubricService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->service = new RubricService;
});

test('getRubricScore returns error when not exactly 5 scores', function () {
    $result = $this->service->getRubricScore([1, 2, 3]);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('5 scores');
});

test('getRubricScore returns success and score when service responds with valid score', function () {
    Http::fake([
        '*' => Http::response(['score' => 75.5], 200),
    ]);

    $result = $this->service->getRubricScore([5, 6, 7, 8, 9]);

    expect($result['success'])->toBeTrue();
    expect($result['score'])->toBe(75.5);
});

test('getRubricScore accepts rubric_score key in response', function () {
    Http::fake([
        '*' => Http::response(['rubric_score' => 80], 200),
    ]);

    $result = $this->service->getRubricScore([6, 7, 8, 9, 10]);

    expect($result['success'])->toBeTrue();
    expect($result['score'])->toBe(80.0);
});

test('getRubricScore parses grade and total_score from model API response', function () {
    Http::fake([
        '*' => Http::response(['grade' => 'B', 'total_score' => 39.0], 200),
    ]);

    $result = $this->service->getRubricScore([8, 7, 9, 8, 7]);

    expect($result['success'])->toBeTrue();
    expect($result['grade'])->toBe('B');
    expect($result['total_score'])->toBe(39.0);
    expect($result['score'])->toBe(39.0);
});

test('getRubricScore returns failure when response is failed', function () {
    Http::fake([
        '*' => Http::response([], 500),
    ]);

    $result = $this->service->getRubricScore([5, 5, 5, 5, 5]);

    expect($result['success'])->toBeFalse();
    expect($result['error'])->toContain('500');
});

test('getRubricScore clamps scores to 1-10', function () {
    Http::fake([
        '*' => Http::response(['score' => 50], 200),
    ]);

    $result = $this->service->getRubricScore([0, 15, -1, 10, 5]);

    expect($result['success'])->toBeTrue();
    Http::assertSent(function ($request) {
        $body = $request->data();

        return ($body['q1_score'] ?? 0) >= 1 && ($body['q1_score'] ?? 0) <= 10
            && ($body['q2_score'] ?? 0) === 10
            && ($body['q3_score'] ?? 0) === 1;
    });
});
