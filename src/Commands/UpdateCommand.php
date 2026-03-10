<?php

namespace ApiCrumbs\Core\Commands;

class UpdateCommand
{
    private string $remoteManifestUrl = 'https://raw.githubusercontent.com/apicrumbs/registry/refs/heads/main/manifest.json';

    public function handle(): void
    {
        echo "📡 Syncing with Remote Registry...\n";

        // 1. Fetch Remote Manifest
        $remoteManifest = json_decode(@file_get_contents($this->remoteManifestUrl), true);
        if (!$remoteManifest) {
            echo "\e[31m❌ Error: Registry unreachable.\e[0m\n";
            return;
        }

        // 2. Load Local State
        $localManifestPath = getcwd() . '/manifest.json';
        $localManifest = file_exists($localManifestPath) ? json_decode(file_get_contents($localManifestPath), true) : [];

        foreach ($remoteManifest as $id => $meta) {
            print_r( $meta );
            $remoteVer = $meta['version'] ?? '1.0.0';
            $localVer  = $localManifest[$id]['version'] ?? '0.0.0';

            // 3. Version Check
            if (version_compare($remoteVer, $localVer, '>')) {
                echo "📦 Update Found: [{$id}] {$localVer} -> \e[32m{$remoteVer}\e[0m\n";
                
                $localPath = $this->resolvePath($meta['class']);
                $this->installWithBackup($id, $meta['download_url'], $localPath, $remoteVer, $localManifest);
            }
        }

        file_put_contents($localManifestPath, json_encode($localManifest, JSON_PRETTY_PRINT));
        echo "\e[32m✨ Update cycle complete.\e[0m\n";
    }

    private function installWithBackup(string $id, string $url, string $path, string $ver, array &$manifest): void
    {
        $backupPath = $path . '.bak';
        $hasBackup = false;

        // Create Backup
        if (file_exists($path)) {
            copy($path, $backupPath);
            $hasBackup = true;
        }

        echo "   📥 Downloading update...";

        // Attempt Download
        if (@copy($url, $path)) {
            echo " \e[32mSuccess\e[0m\n";
            $manifest[$id]['version'] = $ver;
            if ($hasBackup) @unlink($backupPath); // Cleanup
        } else {
            echo " \e[31mFailed\e[0m\n";
            // Restore from Backup if download failed
            if ($hasBackup) {
                rename($backupPath, $path);
                echo "   \e[33m⚠️  Restored from backup.\e[0m\n";
            }
        }
    }

    private function resolvePath(string $class): string
    {
        // Maps ApiCrumbs\Providers\Free\Name to src/Providers/Free/Name.php
        return str_replace(['ApiCrumbs\\', '\\'], ['src/', '/'], $class) . '.php';
    }
}
