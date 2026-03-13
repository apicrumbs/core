<?php

namespace ApiCrumbs\Core\Commands;

use GuzzleHttp\Client;

/**
 * RegistryListCommand - Remote Marketplace Auditor
 * Fetches the global manifest to show available Providers, Agents, and Drivers.
 */
class RegistryListCommand
{
    private string $manifestUrl = 'https://raw.githubusercontent.com/apicrumbs/registry/refs/heads/main/manifest.json';

    public function handle(array $args): void
    {
        echo "🌍 \e[1;36mApiCrumbs Global Registry\e[0m\n";
        echo "Source: ://github.com\n";
        echo str_repeat("-", 60) . "\n";

        $manifest = $this->fetchManifest();
        if (empty($manifest)) return;

        $this->renderSection('Providers', $manifest['providers'] ?? []);
        $this->renderSection('Agents', $manifest['agents'] ?? []);
        $this->renderSection('Drivers', $manifest['drivers'] ?? []);

        echo "\n\e[36m💡 Tip: Install any module using 'php foundry install [type] [id]'\e[0m\n";
    }

    private function renderSection(string $title, array $items): void
    {
        echo "\n\e[1m[" . strtoupper($title) . "]\e[0m\n";
        
        if (empty($items)) {
            echo "  \e[2m(No remote modules found)\e[0m\n";
            return;
        }

        foreach ($items as $id => $meta) {
            $id = $meta['id'];
            $tier = strtoupper($meta['tier'] ?? 'FREE');
            
            // Color coding the tiers for visual conversion
            $color = match($tier) {
                'PRO'    => "\e[34m", // Blue
                'GLOBAL' => "\e[33m", // Gold
                default  => "\e[32m", // Green
            };

            $installed = $this->isInstalled($meta['install_path']) ? "✅" : "  ";
            
            printf("  %s %-25s | v%-8s | %s%s\e[0m\n", 
                $installed, 
                $id, 
                $meta['version'], 
                $color, 
                $tier
            );
        }
    }

    private function isInstalled(string $path): bool
    {
        return file_exists(getcwd() . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
    }

    private function fetchManifest(): array
    {
        try {
            $manifestJson = @file_get_contents("{$this->manifestUrl}");
            return json_decode($manifestJson, true);
        } catch (\Exception $e) {
            echo "❌ \e[31mError: Could not reach Registry. Check your internet connection.\e[0m\n";
            return [];
        }
    }
}