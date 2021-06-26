<?php

declare(strict_types=1);

namespace Deployer;

require 'recipe/symfony.php';

set('ssh_type', 'native');
set('ssh_multiplexing', true);

set('repository', 'git@github.com:ansien/now-inzichtelijk.git');

set('shared_dirs', ['var/log', 'var/sessions']);
set('shared_files', ['.env.local']);
set('writable_dirs', ['var']);

set('clear_paths', [
    './README.md',
    './.gitignore',
    './.git',
    './.env',
    '/ansible',
    '/assets',
    '/tests',
    './symfony.lock',
    './phpunit.xml',
    './phpunit.xml.dist',
    './deploy.php',
    './composer.phar',
    './composer.lock',
]);

set('default_stage', 'production');
set('http_user', 'www-data');

// Tasks
set('bin/yarn', function (): string {
    return run('which yarn');
});

desc('Build assets on server');
task('deploy:assets:build', function () {
    run('cd {{release_path}} && {{bin/yarn}} install && {{bin/yarn}} encore production');
});

// Servers
host('prod')
    ->setHostname('now-inzichtelijk.nl')
    ->setRemoteUser('deploy')
    ->set('branch', 'master')
    ->setForwardAgent(true)
    ->set('deploy_path', '/home/deploy/now-inzichtelijk.nl');

desc('Deploy project');
task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:assets:build',
    'database:migrate',
    'deploy:cache:clear',
    'deploy:cache:warmup',
    'deploy:publish',
]);
