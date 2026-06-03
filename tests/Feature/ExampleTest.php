<?php

test('the health check endpoint is up', function () {
    $this->get('/up')->assertOk();
});

test('the panel at / redirects guests to login', function () {
    // Filament owns "/" and requires authentication.
    $this->get('/')->assertRedirect();
});
