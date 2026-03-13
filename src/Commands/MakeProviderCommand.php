<?php

namespace ApiCrumbs\Core\Commands;

/**
 * MakeProviderCommand - Interactive Provider Scaffolder
 * Generates API or CSV based providers with tier-specific namespaces.
 */
class MakeProviderCommand
{
    public function handle(array $args): void
    {
        $name = $args[2] ?? null;
        if (!$name) {
            echo "\e[31m❌ Error: Specify a name (e.g. php foundry make:provider TrafficData)\e[0m\n";
            return;
        }

        $name = ucwords($name);
        $name = str_replace(" ", "", $name);

        echo $name . PHP_EOL;
        // 1. Detect Flags
        $isCsv = in_array('--csv', $args);
        $tier = $this->resolveTier($args);
        $className = ucfirst($name) . "Provider";
        $namespace = "ApiCrumbs\\Providers\\" . ucfirst($tier);
        $directory = getcwd() . "/src/Providers/" . ucfirst($tier);
        echo $directory . PHP_EOL;
        $filePath = "{$directory}/{$className}.php";

        if (file_exists($filePath)) {
            echo "\e[33m⚠️  Warning: {$className} already exists at {$filePath}\e[0m\n";
            exit(1);
        }
        
        // 2. Interactive Wizard for Metadata
        echo "\e[1;36m🛠️  Scaffolding {$tier} Provider: {$className}\e[0m\n";
        echo "-------------------------------------------\n";
        echo "Enter Provider ID (snake_case, e.g. traffic_stats):\n> ";
        $id = trim(fgets(STDIN));
        
        echo "Enter Dependencies (comma-separated, or leave blank):\n> ";
        $depsInput = trim(fgets(STDIN));
        $deps = !empty($depsInput) ? array_map('trim', explode(',', $depsInput)) : [];
        $formattedDeps = "['" . implode("', '", $deps) . "']";

        // 3. Generate Content
        if (!is_dir($directory)) mkdir($directory, 0755, true);

        $stub = $isCsv 
            ? $this->getCsvStub($namespace, $className, $id, $formattedDeps) 
            : $this->getApiStub($namespace, $className, $id, $formattedDeps);

        if (file_put_contents($filePath, $stub)) {
            echo "\e[32m✨ Success! Created at: {$filePath}\e[0m\n";
            echo "💡 Next: Define your " . ($isCsv ? 'mapping' : 'fetchData') . " logic.\n";
        }
    }

    private function resolveTier(array $args): string
    {
        if (in_array('--global', $args)) return 'global';
        if (in_array('--pro', $args)) return 'pro';
        return 'free';
    }

    private function getApiStub($ns, $class, $id, $deps): string
    {
        return <<<PHP
<?php

namespace {$ns};

use ApiCrumbs\Core\Contracts\BaseProvider;

class {$class} extends BaseProvider
{
    public function getName(): string { return '{$id}'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDependencies(): array { return {$deps}; }

    public function fetchData(string \$id, array \$context = []): array
    {
        // TODO: Implement Guzzle fetch logic
        return [];
    }

    public function transform(array \$data): string
    {
        if (empty(\$data)) return "";
        return "### 🍪 " . strtoupper(\$this->getName()) . PHP_EOL . "---" . PHP_EOL;
    }
}
PHP;
    }

    private function getCsvStub($ns, $class, $id, $deps): string
    {
        return <<<PHP
<?php

namespace {$ns};

use ApiCrumbs\Core\Contracts\CsvStreamProvider;

class {$class} extends CsvStreamProvider
{
    public function getName(): string { return '{$id}'; }
    public function getVersion(): string { return '1.0.0'; }
    public function getDependencies(): array { return {$deps}; }

    protected function getSourceUrl(): string 
    {
        return "https://example.com";
    }

    protected function getMapping(): array
    {
        return [
            'id_column' => 'ID',
            'val_column' => 'Value'
        ];
    }
}
PHP;
    }
}