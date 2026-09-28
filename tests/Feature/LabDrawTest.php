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
