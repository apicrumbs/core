<?php

namespace ApiCrumbs\Core\Commands;

class InstallCommand
{
    private string $registryBase = "https://raw.githubusercontent.com/apicrumbs/registry/refs/heads/main";

    public function handle(array $args): void
    {
        $path = $args[2] ?? null;
        
        if (!$path) {
            echo "\e[31m❌ Error: Provide a path (e.g. php foundry install weather/open-meteo)\e[0m\n";
            exit(1);
        }

        echo "\e[34m🔍 Checking Registry for [{$path}]...\e[0m\n";

        // 1. Check Manifest for Tier
        $manifestJson = @file_get_contents("{$this->registryBase}/manifest.json");
        $manifest = json_decode($manifestJson, true);
        
        $provider = current(array_filter($manifest['providers'] ?? [], fn($p) => $p['id'] === $path));
        
        if (!$provider) {
            echo "\e[31m❌ Error: Provider '{$path}' not found in registry.\e[0m\n";
            exit(1);
        }

        // 2. Enforce Pro Gate
        if ($provider['tier'] === 'pro') {
            echo "\e[1;33m🔒 This is a PRO Crumb.\e[0m\n";
            echo "Unlock the \e[1m{$provider['pack']}\e[0m at: \e[4mhttps://github.com\e[0m\n";
            exit(1);
        }

        $installPath = $provider['install_path'];
        
        // 3. Download and Install
        $remoteUrl = "{$this->registryBase}/{$installPath}";
        
        $code = @file_get_contents($remoteUrl);

        if (!$code) {
            echo "\e[31m❌ Error: Failed to download source code.\e[0m\n";
            exit(1);
        }

        $installPathParts = explode('/', $installPath);
        
        $localPath = getcwd() .'/'. $installPath;
        
        $directory = dirname($localPath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        if (file_put_contents($localPath, $code)) {
            echo "\e[32m✅ Installed: {$provider['name']} Provider\e[0m\n";
            echo "📍 Location: {$localPath}\n";
        }
    }
}