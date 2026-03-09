<?php

namespace ApiCrumbs\Core\Commands;

use ReflectionClass;
use ApiCrumbs\Core\Contracts\ProviderInterface;
use ApiCrumbs\Core\Contracts\BaseProvider;

class LinterCommand
{
    public function handle(): void
    {
        echo "🛡️  Running ApiCrumbs Registry Linter...\n";
        
        $providersDir = getcwd() . '/src/Providers';
        $directory = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($providersDir));
        $errors = 0;

        foreach ($directory as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), 'Provider.php')) continue;

            $className = $this->getClassName($file->getPathname());
            if (!class_exists($className)) {
                require_once $file->getPathname();
            }

            $results = $this->lint($className);
            
            if (!empty($results)) {
                echo "\n❌ \e[31m{$className}\e[0m\n";
                foreach ($results as $err) echo "  - {$err}\n";
                $errors++;
            }
        }

        if ($errors === 0) {
            echo "\e[32m✨ All providers passed architectural linting.\e[0m\n";
        } else {
            exit(1); // Fail for CI/CD
        }
    }

    private function lint(string $class): array
    {
        $issues = [];
        $reflection = new ReflectionClass($class);

        // 1. Interface Check
        if (!$reflection->implementsInterface(ProviderInterface::class)) {
            $issues[] = "Does not implement ProviderInterface.";
        }

        // 2. BaseProvider Check for Pro/Global
        if (str_contains($class, 'Pro') || str_contains($class, 'Global')) {
            if (class_exists(BaseProvider::class) && !$reflection->isSubclassOf(BaseProvider::class)) {
                $issues[] = "Pro/Global providers MUST extend BaseProvider for Throttling.";
            }
        }

        // 3. Method check: Ensure getName() isn't returning an empty string
        $instance = $reflection->newInstanceWithoutConstructor();
        if (empty($instance->getName())) {
            $issues[] = "getName() returns an empty string.";
        }

        // 4. Static Analysis: Check for 'curl_' or 'file_get_contents' (Should use Guzzle/safeFetch)
        $code = file_get_contents($reflection->getFileName());
        if (preg_match('/(curl_init|file_get_contents\(["\']http)/', $code)) {
            $issues[] = "Bypassing Guzzle! Use \$this->safeFetch() instead for consistency.";
        }

        return $issues;
    }

    private function getClassName($path): string {
        // Simple logic to resolve FQCN from path
        $path = str_replace([getcwd().'/src/', '.php', '/'], ['', '', '\\'], $path);
        return "ApiCrumbs\\" . $path;
    }
}
