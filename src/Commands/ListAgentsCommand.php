<?php

namespace ApiCrumbs\Core\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * ListAgentsCommand - Expert Brain Inventory
 * Scans src/Agents and lists metadata and data requirements.
 */
class ListAgentsCommand
{
    public function handle(): void
    {
        $baseDir = getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Agents';
        
        echo "\e[1;35m🧠 ApiCrumbs Expert Agent Directory\e[0m\n";
        echo str_repeat("-", 70) . "\n";
        printf(" %-20s | %-8s | %-10s | %-20s\n", "Agent ID", "Version", "Tier", "Required Crumbs");
        echo str_repeat("-", 70) . "\n";

        if (!is_dir($baseDir)) {
            echo "⚠️  No agents found in src/Agents. Use 'foundry install agent [id]'.\n";
            return;
        }

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
        $count = 0;

        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;

            $className = $this->resolveNamespace($file->getRealPath());

            if (class_exists($className)) {
                $instance = new $className($this->mockDriver());
                $this->renderRow($instance, $file->getRealPath());
                $count++;
            }
        }

        echo str_repeat("-", 70) . "\n";
        echo "Total: \e[1;35m{$count} Agents\e[0m available for orchestration.\n";
    }

    private function resolveNamespace(string $path): string
    {
        $relative = str_replace(getcwd() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, '', $path);
        $nsPath = 'ApiCrumbs\\' . str_replace('.php', '', $relative);
        return str_replace(DIRECTORY_SEPARATOR, '\\', $nsPath);
    }

    private function renderRow($instance, string $fullPath): void
    {
        $name = $instance->getName();
        $version = $instance->getVersion();
        $crumbs = implode(', ', $instance->getRequiredCrumbs());
        
        $tier = "Free";
        if (str_contains($fullPath, 'Pro')) $tier = "\e[35mPro\e[0m";
        if (str_contains($fullPath, 'Global')) $tier = "\e[33mGlobal\e[0m";

        printf(" %-20s | %-8s | %-10s | %-20s\n", $name, $version, $tier, $crumbs);
    }

    private function mockDriver() {
        // Simple mock to allow instantiation without a real LLM key
        return new class implements \ApiCrumbs\Core\Contracts\AgentDriverInterface {
            public function execute(array $i, string $c, string $q): string { return ''; }
        };
    }
}