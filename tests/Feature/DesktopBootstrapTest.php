<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DesktopBootstrapTest extends TestCase
{
    public function test_health_endpoint_responds_ok_for_desktop_launcher(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_bootstrap_app_honors_desktop_env_storage_and_windows_cache_paths(): void
    {
        $tempRoot = sys_get_temp_dir().DIRECTORY_SEPARATOR.'guru_desktop_boot_'.uniqid();
        $envPath = $tempRoot.DIRECTORY_SEPARATOR.'env';
        $storagePath = $tempRoot.DIRECTORY_SEPARATOR.'storage';
        $cachePath = $tempRoot.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'packages.php';

        File::ensureDirectoryExists($envPath);
        File::ensureDirectoryExists($storagePath);
        File::ensureDirectoryExists(dirname($cachePath));

        $code = <<<'PHP'
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
echo json_encode([
    'env' => $app->environmentPath(),
    'storage' => $app->storagePath(),
    'packages' => $app->getCachedPackagesPath(),
]);
PHP;

        try {
            $process = new Process([PHP_BINARY, '-r', $code], base_path(), [
                'LARAVEL_ENV_PATH' => $envPath,
                'LARAVEL_STORAGE_PATH' => $storagePath,
                'APP_PACKAGES_CACHE' => $cachePath,
                'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\\Windows',
                'TEMP' => sys_get_temp_dir(),
                'TMP' => sys_get_temp_dir(),
            ]);
            $process->mustRun();

            $decoded = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame($envPath, $decoded['env']);
            $this->assertSame($storagePath, $decoded['storage']);
            $this->assertSame($cachePath, $decoded['packages']);
        } finally {
            File::deleteDirectory($tempRoot);
        }
    }
}
