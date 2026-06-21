<?php

test('the health check endpoint is up', function () {
    $this->get('/up')->assertOk();
});

test('the public homepage is reachable by guests', function () {
    // Filament admin lives at /console; "/" is now the public marketing site.
    $this->get('/')->assertOk();
});

test('the admin panel at /console redirects guests to login', function () {
    $this->get('/console')->assertRedirect();
});
