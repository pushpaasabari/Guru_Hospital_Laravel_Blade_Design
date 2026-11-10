<?php

/**
 * Launch Guru Hospital using PHP's built-in server with OPcache enabled
 * automatically when available (without modifying host php.ini).
 */

$rootDir = dirname(__DIR__, 2);
$publicDir = $rootDir.DIRECTORY_SEPARATOR.'public';
$router = $rootDir.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'laravel'.DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Illuminate'.DIRECTORY_SEPARATOR.'Foundation'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'server.php';

if (! is_file($router)) {
    fwrite(STDERR, "Laravel server router not found. Run composer install first.\n");
    exit(1);
}

$host = $argv[1] ?? (getenv('SERVER_HOST') ?: '127.0.0.1');
$port = $argv[2] ?? (getenv('SERVER_PORT') ?: '8000');

$args = [PHP_BINARY];

if (! extension_loaded('Zend OPcache')) {
    $extDir = ini_get('extension_dir') ?: (PHP_OS_FAMILY === 'Windows' ? dirname(PHP_BINARY).DIRECTORY_SEPARATOR.'ext' : '');
    $opcacheLib = PHP_OS_FAMILY === 'Windows' ? 'php_opcache.dll' : 'opcache.so';
    $opcachePath = $extDir ? rtrim($extDir, '/\\').DIRECTORY_SEPARATOR.$opcacheLib : $opcacheLib;

    if (is_file($opcachePath)) {
        $args[] = '-d';
        $args[] = 'zend_extension='.$opcachePath;
    }
}

foreach ([
    'opcache.enable=1',
    'opcache.enable_cli=1',
    'opcache.memory_consumption=128',
    'opcache.interned_strings_buffer=16',
    'opcache.max_accelerated_files=16000',
    'opcache.validate_timestamps=1',
    'opcache.revalidate_freq=2',
] as $iniDirective) {
    $args[] = '-d';
    $args[] = $iniDirective;
}

$args[] = '-S';
$args[] = "{$host}:{$port}";
$args[] = $router;

$escaped = array_map('escapeshellarg', $args);
$command = implode(' ', $escaped);

chdir($publicDir);
passthru($command, $exitCode);
exit((int) $exitCode);

