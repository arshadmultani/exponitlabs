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

// ---------------------------------------------------------------------------
// Pull production data down to local (one-way; prod is the source of truth).
// ---------------------------------------------------------------------------

// Builds an "scp" command using the current host's SSH settings. The shared host
// has no rsync, so Deployer's download() (rsync) can't be used — scp runs locally.
function scpFrom(string $remotePath, string $localPath, bool $recursive = false): void
{
    $host = currentHost();
    runLocally(sprintf(
        'scp %s -P %s -i %s %s@%s:%s %s',
        $recursive ? '-r' : '',
        $host->get('port'),
        $host->get('identity_file'),
        $host->get('remote_user'),
        $host->getHostname(),
        $remotePath,
        $localPath,
    ));
}

// Overwrites local database/database.sqlite with a consistent snapshot of prod.
task('db:pull', function () {
    $snapshot = '/tmp/exponit-db-pull.sqlite';

    // cd first so the shell expands "~" in {{deploy_path}}; then use a relative
    // path. ".backup" takes a consistent copy even while the app is live (WAL-safe).
    run('cd {{deploy_path}} && sqlite3 shared/database/database.sqlite ".backup \''.$snapshot.'\'"');
    scpFrom($snapshot, 'database/database.sqlite');
    run("rm -f $snapshot");

    writeln('<info>Pulled production database → database/database.sqlite</info>');
})->desc('Download the production SQLite database to local (overwrites local DB)');

// Mirrors prod uploaded media (product/news images, etc.) into local storage.
task('media:pull', function () {
    runLocally('mkdir -p storage/app/public');
    // ~ in deploy_path expands on the remote shell for scp; copy dir contents.
    $base = currentHost()->get('deploy_path');
    scpFrom($base.'/shared/storage/app/public/.', 'storage/app/public/', recursive: true);
    writeln('<info>Pulled production media → storage/app/public</info>');
})->desc('Download production uploaded media to local');

// Convenience: DB + media in one go.
task('pull', ['db:pull', 'media:pull'])
    ->desc('Pull production database and media to local');
