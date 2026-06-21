<?php

namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/arshadmultani/exponitlabs.git');

set('keep_releases', 3);

set('writable_mode', 'chmod');

set('shared_files', [
    '.env',
    'version.txt',
    'database/database.sqlite',
]);
set('shared_dirs', [
    'storage',
]);
set('writable_dirs', [
    'storage',
    'bootstrap/cache',
]);

set('copy_dirs', [
    'public/build',
]);

set(
    'composer_options',
    '--prefer-dist --no-dev --optimize-autoloader'
);
// Hosts

host('exponit.com')
    ->set('remote_user', 'exponit')
    ->set('deploy_path', '~/exponitlabs')
    ->set('port', 22999)
    ->setIdentityFile('~/.ssh/cpanel_deploy')
    ->set('writable_mode', 'skip');

// Version Bumping
task('version:bump', function () {

    $path = '{{deploy_path}}/shared/version.txt';

    $current = trim(run("
        if [ -f $path ]; then
            cat $path
        else
            echo 0.000
        fi
    "));

    $next = number_format(
        ((float) $current) + 0.001,
        3,
        '.',
        ''
    );

    run("echo '$next' > $path");

    writeln("App version: $next");
});

// Hooks

before('deploy:update_code', function () {

    runLocally('npm ci');

    runLocally('npm run build');

});
before('deploy:publish', 'version:bump');

after('deploy:failed', 'deploy:unlock');

after('deploy:cleanup', 'artisan:cache:clear');
after('deploy:cleanup', 'artisan:optimize');

after('version:bump', 'version:sync_local');

task('version:sync_local', function () {
    $version = trim(run('cat {{deploy_path}}/shared/version.txt'));
    runLocally("echo '$version' > version.txt");
    writeln("Local version.txt synced to: $version");
});
