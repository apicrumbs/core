<?php

namespace ApiCrumbs\Core\Commands;

/**
 * ListCommand - Local Inventory Auditor
 * Scans the src/ directory to display installed modules and their tiers.
 */
class ListCommand
{
    public function handle(array $args): void
    {
        echo "📂 \e[1;36mApiCrumbs Local Inventory\e[0m\n";
        echo str_repeat("-", 55) . "\n";

        $this->listModules('providers', 'src/Providers');
        $this->listModules('agents', 'src/Agents');
        $this->listModules('drivers', 'src/Drivers');

        echo "\n\e[36m💡 Tip: Run 'foundry update' to check for newer versions.\e[0m\n";
    }

    private function listModules(string $label, string $path): void
    {
        $fullPath = getcwd() . DIRECTORY_SEPARATOR . $path;
        
        echo "\n\e[1m[" . strtoupper($label) . "]\e[0m\n";

        if (!is_dir($fullPath)) {
            echo "  \e[2m(No modules installed in this category)\e[0m\n";
            return;
        }

        // Recursive directory iterator to find all PHP classes
        $directory = new \RecursiveDirectoryIterator($fullPath);
        $iterator = new \RecursiveIteratorIterator($directory);
        $files = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

        $found = false;
        foreach ($files as $file) {
            $content = file_get_contents($file[0]);
            $name = basename($file[0], '.php');
            
            // Extract Version and Tier from the class content
            $version = $this->pluck($content, "getVersion") ?: '1.0.0';
            $tier = $this->guessTier($file[0]);

            $tierColor = $tier === 'pro' ? "\e[34m" : ($tier === 'global' ? "\e[33m" : "\e[32m");
            
            printf("  %-25s | v%-8s | %s%s\e[0m\n", $name, $version, $tierColor, strtoupper($tier));
            $found = true;
        }

        if (!$found) echo "  \e[2m(Empty)\e[0m\n";
    }

    private function pluck(string $content, string $method): ?string
    {
        if (preg_match("/public function {$method}\(\): string { return '(.+?)'; }/", $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function guessTier(string $path): string
    {
        if (str_contains($path, 'Pro')) return 'pro';
        if (str_contains($path, 'Global')) return 'global';
        return 'free';
    }
}