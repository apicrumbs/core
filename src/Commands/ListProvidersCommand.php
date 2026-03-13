<?php

namespace ApiCrumbs\Core\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ListProvidersCommand
{
    public function handle(): void
    {
        // Path to your providers
        $baseDir = getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Providers';
        
        echo "\e[1;36m📦 ApiCrumbs Local Provider Inventory\e[0m\n";
        echo str_repeat("-", 60) . "\n";
        printf(" %-32s | %-8s | %-5s | %-8s\n", "Provider ID", "Version", "Tier", "Type");
        echo str_repeat("-", 60) . "\n";

        if (!is_dir($baseDir)) {
            echo "❌ Error: Directory not found: {$baseDir}\n";
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
        $count = 0;

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;

            // Convert File Path to Namespace (The fix)
            $className = $this->resolveNamespace($file->getRealPath());

            if (class_exists($className)) {
                $instance = new $className();
                $this->renderRow($instance, $file->getRealPath());
                $count++;
            } else {
                // Debugging: If class isn't found, it's a namespace issue
                echo "⚠️  Could not load: {$className} (Check namespace in file)\n";
            }
        }

        echo str_repeat("-", 60) . "\n";
        echo "Total: \e[1;32m{$count} Providers\e[0m ready to stitch.\n";
    }

    private function resolveNamespace(string $path): string
    {
        // 1. Get the path relative to 'src'
        // Example: C:\wamp\www\apicrumbs\src\Providers\Free\PostcodeProvider.php
        $relative = str_replace(getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, '', $path);
        
        // 2. Prepend the Root Namespace and strip .php
        // Result: Providers\Free\PostcodeProvider
        $nsPath = 'ApiCrumbs\\' . str_replace('.php', '', $relative);
        
        // 3. Normalize slashes for PHP
        return str_replace(DIRECTORY_SEPARATOR, '\\', $nsPath);
    }

    private function renderRow($instance, string $fullPath): void
    {
        $name = $instance->getName();
        $version = $instance->getVersion();
        
        // Determine Tier by folder name
        $tier = "Free ";
        if (str_contains($fullPath, 'Pro')) $tier = "\e[35mPro  \e[0m";
        
        $type = is_subclass_of($instance, 'ApiCrumbs\Core\Contracts\CsvStreamProvider') ? 'CSV' : 'API';

        printf(" %-32s | %-8s | %-4s | %-8s\n", $name, $version, $tier, $type);
    }
}