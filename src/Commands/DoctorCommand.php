<?php

namespace ApiCrumbs\Core\Commands;

/**
 * DoctorCommand - System Health & Environment Diagnostics
 * Validates PHP version, SSL bundles, extensions, and disk permissions.
 */
class DoctorCommand
{
    public function handle(): void
    {
        echo "\e[1;36m🩺 ApiCrumbs System Diagnosis\e[0m\n";
        echo "-------------------------------\n";

        $this->checkPhpVersion();
        $this->checkSslHealth();
        $this->checkExtensions();
        $this->checkDiskPermissions();
        $this->checkEnvStatus();

        echo "\n\e[36m💡 Tip: Run 'foundry setup:all' to fix most red marks.\e[0m\n";
    }

    private function checkPhpVersion(): void
    {
        $version = PHP_VERSION;
        $req = '8.1.0';
        $status = version_compare($version, $req, '>=') ? "✅" : "❌";
        echo "{$status} PHP Version: {$version} (Min: {$req})\n";
    }

    private function checkSslHealth(): void
    {
        $localPem = getcwd() . DIRECTORY_SEPARATOR . 'cacert.pem';
        $iniPath = ini_get('curl.cainfo') ?: ini_get('openssl.cafile');

        if (file_exists($localPem)) {
            echo "✅ SSL: Local 'cacert.pem' detected in project root (Priority).\n";
        } elseif (!empty($iniPath) && file_exists($iniPath)) {
            echo "✅ SSL: System CA bundle found at {$iniPath}\n";
        } else {
            echo "❌ SSL: No CA bundle found. HTTPS requests will fail.\n";
        }
    }

    private function checkExtensions(): void
    {
        $exts = ['curl', 'json', 'openssl', 'mbstring'];
        foreach ($exts as $ext) {
            $status = extension_loaded($ext) ? "✅" : "❌";
            echo "{$status} Extension: [{$ext}] " . (extension_loaded($ext) ? "active" : "MISSING") . "\n";
        }
    }

    private function checkDiskPermissions(): void
    {
        $dirs = [
            'root' => getcwd(),
            'providers' => getcwd() . '/src/Providers',
            'agents' => getcwd() . '/src/Agents'
        ];

        foreach ($dirs as $label => $path) {
            if (is_dir($path)) {
                $status = is_writable($path) ? "✅" : "❌";
                echo "{$status} Disk: [{$label}] is " . (is_writable($path) ? "writable" : "LOCKED") . "\n";
            }
        }
    }

    private function checkEnvStatus(): void
    {
        $hasEnv = file_exists(getcwd() . '/.env');
        $status = $hasEnv ? "✅" : "⚠️ ";
        echo "{$status} Config: .env file " . ($hasEnv ? "detected" : "NOT FOUND") . "\n";
        
        if ($hasEnv && empty(getenv('APICRUMBS_PRO_TOKEN'))) {
            echo "   \e[33m(Note: No PRO_TOKEN found. Registry limited to Free tier.)\e[0m\n";
        }
    }
}