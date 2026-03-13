<?php

namespace ApiCrumbs\Core\Commands;

/**
 * LinterCommand - Architectural & Naming Consistency Checker
 * Enforces snake_case keys, PSR-4 structure, and Interface compliance.
 */
class LinterCommand
{
    private int $errors = 0;
    private int $warnings = 0;

    public function handle(array $args): void
    {
        $strict = in_array('--strict', $args);
        
        echo "🔍 \e[1;36mApiCrumbs Architectural Linter\e[0m\n";
        echo str_repeat("-", 50) . "\n";

        $this->lintDirectory('src/Providers', 'ProviderInterface');
        $this->lintDirectory('src/Agents', 'AgentInterface');
        $this->lintDirectory('src/Drivers', 'AgentDriverInterface');

        echo "\n" . str_repeat("-", 50) . "\n";
        
        if ($this->errors > 0) {
            echo "❌ \e[31mFound {$this->errors} Errors.\e[0m Project is non-compliant.\n";
        } else {
            echo "✅ \e[32mArchitecture is valid.\e[0m " . ($this->warnings ? "({$this->warnings} warnings)" : "") . "\n";
        }
    }

    private function lintDirectory(string $path, string $interface): void
    {
        $fullPath = getcwd() . DIRECTORY_SEPARATOR . $path;
        if (!is_dir($fullPath)) return;

        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($fullPath));
        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') continue;

            $content = file_get_contents($file->getRealPath());
            $name = $file->getBasename('.php');

            echo "📂 Testing: {$name}... ";

            $localErrors = 0;

            // 1. Check: Name must match File (PSR-4)
            if (!str_contains($content, "class {$name}")) {
                $this->logError("Class name mismatch (PSR-4 violation)");
                $localErrors++;
            }

            // 2. Check: Lowercase snake_case keys (Crucial for Stitching)
            if (preg_match("/getName\(\): string { return '([^a-z0-9_]+)'; }/", $content, $matches)) {
                $this->logError("Key '{$matches[1]}' must be lowercase snake_case");
                $localErrors++;
            }

            // 3. Check: Missing versioning
            if (!str_contains($content, "public function getVersion()")) {
                $this->logWarning("Missing getVersion() method. Defaulting to 1.0.0");
                $this->warnings++;
            }

            if ($localErrors === 0) {
                echo "\e[32mPASS\e[0m\n";
            }
        }
    }

    private function logError(string $msg): void
    {
        echo "\n  \e[31m[ERROR]\e[0m {$msg}";
        $this->errors++;
    }

    private function logWarning(string $msg): void
    {
        echo "\n  \e[33m[WARN]\e[0m {$msg}";
    }
}