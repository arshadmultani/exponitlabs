<?php

test('lab draw route is publicly accessible and returns 200', function () {
    $response = $this->get('/lab/draw');

    $response->assertStatus(200);
    $response->assertViewIs('lab.draw');
    $response->assertSee('Exponit Draw');
    $response->assertSee('drawing-canvas');
});

test('root service worker explicitly bypasses lab routes', function () {
    $swContent = file_get_contents(public_path('sw.js'));

    expect($swContent)->toContain("url.pathname.startsWith('/lab/')");
});
