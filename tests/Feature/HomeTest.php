<?php

test('home route returns successful response', function () {
    $response = $this->get(route('home'));

    $response->assertStatus(200);
});
