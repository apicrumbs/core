<?php

namespace ApiCrumbs\Core\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * UseDriverCommand - Global Transport Selector
 * Scans local drivers and updates .env with the chosen ID.
 */
class UseDriverCommand
{
    public function handle(array $args): void
    {
        $driverId = $args[2] ?? null;

        if (!$driverId) {
            echo "❌ \e[31mError: Provide a Driver ID (e.g. php foundry use:driver openai)\e[0m\n";
            return;
        }

        // 1. Scan src/Core/Drivers to verify existence
        $installedDrivers = $this->getInstalledDrivers();
        
        if (!in_array($driverId, $installedDrivers)) {
            echo "❌ \e[31mError: Driver '{$driverId}' is not installed locally.\e[0m\n";
            echo "💡 Run '\e[1mphp foundry list:drivers\e[0m' to see available options.\n";
            return;
        }

        // 2. Update .env file
        $envPath = getcwd() . DIRECTORY_SEPARATOR . '.env';
        if (!file_exists($envPath)) {
            echo "❌ \e[31mError: .env file missing. Run 'foundry setup:env' first.\e[0m\n";
            return;
        }

        $content = file_get_contents($envPath);
        $pattern = '/AICRUMBS_LLM_DRIVER=.*/';
        $replacement = "AICRUMBS_LLM_DRIVER={$driverId}";

        // If the key exists, replace it; otherwise, append it.
        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, $replacement, $content);
        } else {
            $newContent = $content . "\nAICRUMBS_LLM_DRIVER={$driverId}";
        }

        if (file_put_contents($envPath, $newContent)) {
            echo "🔌 \e[32mGlobal Transport Updated!\e[0m\n";
            echo "✅ Default Driver is now: \e[1m{$driverId}\e[0m\n";
        }
    }

    private function getInstalledDrivers(): array
    {
        $dir = getcwd() . '/src/Drivers';
        if (!is_dir($dir)) return [];

        $drivers = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;
            // Clean up the name: OpenAiDriver.php -> openai
            $drivers[] = strtolower(str_replace(['Driver', '.php'], '', $file->getFilename()));
        }
        return $drivers;
    }
}