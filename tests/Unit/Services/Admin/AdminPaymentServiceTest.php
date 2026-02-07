<?php

use App\Models\Institution;
use App\Models\Payment;
use App\Services\Admin\AdminPaymentService;
use Illuminate\Http\Request;

beforeEach(function () {
    $this->service = new AdminPaymentService;
});

test('getPaymentsWithFilters returns paginated payments and stats', function () {
    $institution = Institution::create([
        'name' => 'School',
        'slug' => 'school',
        'email' => 'admin@school.edu',
        'is_active' => true,
    ]);
    Payment::create([
        'institution_id' => $institution->id,
        'amount' => 10000,
        'currency' => 'USD',
        'status' => 'completed',
        'gateway' => 'stripe',
        'external_id' => 'ext_1',
        'paid_at' => now(),
    ]);

    $request = Request::create('/', 'GET');
    $result = $this->service->getPaymentsWithFilters($request);

    expect($result)->toHaveKeys(['payments', 'stats', 'filters']);
    expect($result['stats'])->toHaveKeys(['total', 'completed', 'pending', 'total_amount']);
    expect($result['stats']['total'])->toBeGreaterThan(0);
    expect($result['payments']->count())->toBeGreaterThan(0);
});

test('getPaymentsWithFilters filters by status when provided', function () {
    $request = Request::create('/', 'GET', ['status' => 'pending']);
    $result = $this->service->getPaymentsWithFilters($request);

    expect($result['filters']['status'])->toBe('pending');
});
