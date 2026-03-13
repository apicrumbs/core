<?php

namespace ApiCrumbs\Core\Commands;

use GuzzleHttp\Client;

/**
 * SslSetupCommand - Automated CA Bundle Localization
 * Downloads the latest cacert.pem to the project root for XAMPP compatibility.
 */
class SslSetupCommand
{
    private string $caUrl = "https://curl.se";
    private string $fileName = "cacert.pem";

    public function handle(): void
    {
        $targetPath = getcwd() . DIRECTORY_SEPARATOR . $this->fileName;

        echo "🔐 \e[1;36mApiCrumbs SSL Localizer\e[0m\n";
        echo "--------------------------\n";

        // 1. Check if already exists
        if (file_exists($targetPath)) {
            echo "ℹ️  '{$this->fileName}' already exists in root. Skipping download.\n";
            return;
        }

        echo "⏳ Downloading latest CA bundle from curl.se... ";

        try {
            // Create a bootstrap client with verification disabled to fetch the "cure"
            $client = new Client(['verify' => false, 'timeout' => 10.0]);
            $response = $client->get($this->caUrl);
            
            $pemData = $response->getBody()->getContents();

            if (empty($pemData)) {
                throw new \Exception("Downloaded file is empty.");
            }

            // 2. Save to Project Root
            if (file_put_contents($targetPath, $pemData)) {
                echo "\e[32mDone\e[0m\n";
                echo "✨ Success! The Core will now automatically detect this local bundle.\n";
            } else {
                throw new \Exception("Could not write to disk. Check folder permissions.");
            }
        } catch (\Exception $e) {
            echo "\e[31mFailed\e[0m\n";
            echo "❌ Error: {$e->getMessage()}\n";
            echo "💡 Tip: Manually download from https://curl.se and place in root.\n";
        }
    }
}