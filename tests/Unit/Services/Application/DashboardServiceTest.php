<?php

use App\Models\User;
use App\Services\Application\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

beforeEach(function () {
    $this->service = new DashboardService;
});

test('getDashboardRouteForRole returns admin.dashboard for admin', function () {
    expect($this->service->getDashboardRouteForRole('admin'))->toBe('admin.dashboard');
});

test('getDashboardRouteForRole returns student.dashboard for student', function () {
    expect($this->service->getDashboardRouteForRole('student'))->toBe('student.dashboard');
});

test('getDashboardRouteForRole returns lecturer.dashboard for lecturer', function () {
    expect($this->service->getDashboardRouteForRole('lecturer'))->toBe('lecturer.dashboard');
});

test('getDashboardRouteForRole returns institution.dashboard for institution', function () {
    expect($this->service->getDashboardRouteForRole('institution'))->toBe('institution.dashboard');
});

test('getDashboardRouteForRole returns student.dashboard for unknown role', function () {
    expect($this->service->getDashboardRouteForRole('unknown'))->toBe('student.dashboard');
});

test('getRedirectForCurrentUser redirects to login when not authenticated', function () {
    Auth::shouldReceive('user')->andReturn(null);
    $redirectResponse = new \Illuminate\Http\RedirectResponse('/login');
    Redirect::shouldReceive('route')->with('login')->andReturn($redirectResponse);

    $response = $this->service->getRedirectForCurrentUser();

    expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
});

test('getRedirectForCurrentUser redirects to role dashboard when authenticated', function () {
    $user = User::factory()->make(['role' => 'student']);
    Auth::shouldReceive('user')->andReturn($user);
    $redirectResponse = new \Illuminate\Http\RedirectResponse('/student/dashboard');
    Redirect::shouldReceive('route')->with('student.dashboard')->andReturn($redirectResponse);

    $response = $this->service->getRedirectForCurrentUser();

    expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class);
});
