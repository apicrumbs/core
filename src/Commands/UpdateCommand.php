<?php

namespace ApiCrumbs\Core\Commands;

use GuzzleHttp\Client;

class UpdateCommand
{
    private string $manifestUrl = 'https://raw.githubusercontent.com/apicrumbs/registry/refs/heads/main/manifest.json';
    private string $registryBase = "https://raw.githubusercontent.com/apicrumbs/registry/refs/heads/main/";

    public function handle(array $args): void
    {
        $isDryRun = in_array('--dry-run', $args);
        echo "🔄 \e[1;36mChecking Registry for Updates...\e[0m\n";

        $manifest = $this->fetchManifest();
        $categories = ['providers', 'agents', 'drivers'];
        $updatesFound = 0;

        foreach ($categories as $cat) {
            foreach ($manifest[$cat] as $id => $item) {
                $localPath = getcwd() . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $item['install_path']);

                if (file_exists($localPath)) {
                    $localVersion = $this->extractLocalVersion($localPath);
                    
                    if (version_compare($localVersion, $item['version'], '<')) {
                        $updatesFound++;
                        echo "  ✨ Update Available: \e[1m{$id}\e[0m (v{$localVersion} -> v{$item['version']})\n";

                        if (!$isDryRun) {
                            $this->performUpgrade($item, $localPath);
                        }
                    }
                }
            }
        }

        echo $updatesFound === 0 ? "✅ \e[32mAll modules up to date.\e[0m\n" : "\n🚀 \e[32mSync complete.\e[0m\n";
    }

    private function extractLocalVersion(string $path): string
    {
        $content = file_get_contents($path);
        if (preg_match("/public function getVersion\(\): string { return '(.+?)'; }/", $content, $matches)) {
            return $matches[1];
        }
        return '0.0.0';
    }

    private function performUpgrade(array $item, string $path): void
    {
        $sourceUrl = $this->registryBase . $item['install_path'];        
        $content = file_get_contents($sourceUrl);
        file_put_contents($path, $content);
        echo "    📦 \e[32mUpgraded {$item['name']}\e[0m\n";
    }

    private function fetchManifest(): array
    {
        $manifestJson = @file_get_contents("{$this->manifestUrl}");
        return json_decode($manifestJson, true);
    }
}