<?php

test('lab draw route is publicly accessible and returns 200', function () {
    $response = $this->get('/lab/draw');

    $response->assertStatus(200);
    $response->assertViewIs('lab.draw');
    $response->assertSee('Exponit Draw');
    $response->assertSee('drawing-canvas');
    $response->assertSee('lab/draw/js/tf.min.js');
});

test('root service worker explicitly bypasses lab routes', function () {
    $swContent = file_get_contents(public_path('sw.js'));

    expect($swContent)->toContain("url.pathname.startsWith('/lab/')");
});

test('local tensorflow and model files are present for offline ai inference', function () {
    expect(file_exists(public_path('lab/draw/js/tf.min.js')))->toBeTrue();
    expect(file_exists(public_path('lab/draw/model/model.json')))->toBeTrue();
    expect(file_exists(public_path('lab/draw/model/group1-shard1of1.bin')))->toBeTrue();
    expect(file_exists(public_path('lab/draw/model/class_names.txt')))->toBeTrue();

    $modelJson = json_decode(file_get_contents(public_path('lab/draw/model/model.json')), true);
    expect($modelJson)->toHaveKey('modelTopology');
    expect($modelJson)->toHaveKey('weightsManifest');
});

test('pwa manifest exists and is properly linked in lab draw view', function () {
    $response = $this->get('/lab/draw');
    $response->assertSee('lab/draw/manifest.webmanifest');
    $response->assertSee('lab/draw/assets/icons/icon-192.png');

    $manifestPath = public_path('lab/draw/manifest.webmanifest');
    expect(file_exists($manifestPath))->toBeTrue();

    $manifest = json_decode(file_get_contents($manifestPath), true);
    expect($manifest['name'])->toBe('Exponit Draw');
    expect($manifest['display'])->toBe('standalone');
    expect($manifest['orientation'])->toBe('landscape');
    expect($manifest['start_url'])->toBe('/lab/draw');
    expect($manifest['icons'])->toHaveCount(2);
});

test('dedicated service worker is served with Service-Worker-Allowed header and precaches critical assets', function () {
    $response = $this->get('/lab/draw/sw.js');
    $response->assertStatus(200);
    $response->assertHeader('Service-Worker-Allowed', '/lab/draw');
    $response->assertHeader('Content-Type', 'application/javascript');

    $swContent = file_get_contents(public_path('lab/draw/sw.js'));
    expect($swContent)->toContain('lab-draw-cache-v1');
    expect($swContent)->toContain('/lab/draw/model/model.json');
    expect($swContent)->toContain('/lab/draw/model/group1-shard1of1.bin');
    expect($swContent)->toContain('/lab/draw/js/tf.min.js');
    expect($swContent)->toContain('/lab/draw/css/draw.css');
});
