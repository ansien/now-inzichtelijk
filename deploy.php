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
    './.php_cs',
    './.env',
    './.eslintrc',
    '/assets',
    '/tests',
    './package.json',
    './package-lock.json',
    './symfony.lock',
    './webpack.config.js',
    './phpunit.xml',
    './phpunit.xml.dist',
    './deploy.php',
    './psalm.xml',
    './composer.phar',
    './composer.lock',
]);

set('default_stage', 'production');
set('http_user', 'www-data');

// Servers
host('production')
    ->setHostname('now-inzichtelijk.nl')
    ->setRemoteUser('deploy')
    ->set('branch', 'master')
    ->setForwardAgent(true)
    ->set('deploy_path', '/home/deploy/now-inzichtelijk.nl');

set('bin/yarn', function () {
    return (string) run('which yarn');
});
desc('Build assets on server');
task('deploy:assets:build', function () {
    run('cd {{release_path}} && {{bin/yarn}} install && {{bin/yarn}} encore production');
});

desc('Clean redis');
task('deploy:redis:clear', static function () {
    run('redis-cli FLUSHALL');
});

task('deploy', [
    'deploy:prepare',
    'deploy:vendors',
    'deploy:assets:build',
    'deploy:cache:clear',
    'deploy:redis:clear',
    'deploy:cache:warmup',
    'deploy:publish',
])->desc('Deploy');
