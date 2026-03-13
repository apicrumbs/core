<?php

namespace ApiCrumbs\Core\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * ListDriversCommand - Transport & LLM Inventory
 * Scans src/Core/Drivers and lists available LLM transports.
 */
class ListDriversCommand
{
    public function handle(): void
    {
        $baseDir = getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Drivers';
        
        echo "\e[1;32m🔌 ApiCrumbs Driver & Transport Inventory\e[0m\n";
        echo str_repeat("-", 65) . "\n";
        printf(" %-18s | %-10s | %-10s | %-15s\n", "Driver ID", "Tier", "Type", "Endpoint");
        echo str_repeat("-", 65) . "\n";

        if (!is_dir($baseDir)) {
            echo "⚠️  No drivers found. Run 'foundry install driver llm/openai'.\n";
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
        $count = 0;

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;

            $className = $this->resolveNamespace($file->getRealPath());

            if (class_exists($className)) {
                // Drivers usually have empty constructors or optional config
                $instance = new $className();
                $this->renderRow($instance, $file->getRealPath());
                $count++;
            }
        }

        echo str_repeat("-", 65) . "\n";
        echo "Total: \e[1;32m{$count} Drivers\e[0m installed locally.\n";
    }

    private function resolveNamespace(string $path): string
    {
        $relative = str_replace(
            [getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, '.php'], 
            '', 
            $path
        );
        $nsPath = 'ApiCrumbs\\' . $relative;
        return str_replace(DIRECTORY_SEPARATOR, '\\', $nsPath);
    }

    private function renderRow($instance, string $fullPath): void
    {
        // We use Reflection or public properties to peek at the Driver's target
        $reflect = new \ReflectionClass($instance);
        $name = strtolower(str_replace('Driver', '', $reflect->getShortName()));
        
        $tier = "Free";
        if (str_contains($fullPath, 'Pro')) $tier = "\e[35mPro\e[0m";
        
        // Logic to determine if it's Cloud or Local
        $type = (str_contains($name, 'ollama') || str_contains($name, 'llama')) ? 'Local' : 'Cloud';
        
        $endpoint = "Global API";
        if ($type === 'Local') $endpoint = getenv('OLLAMA_HOST') ?: 'localhost:11434';

        printf(" %-18s | %-10s | %-10s | %-15s\n", $name, $tier, $type, $endpoint);
    }
}