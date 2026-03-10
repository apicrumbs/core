<?php

namespace ApiCrumbs\Core\Commands;

class MakeCommand
{
    public function handle(array $args): void
    {
        $name = $args[2] ?? null;
        $tierFlag = $args[3] ?? '--free';
        $isCsv = in_array('--csv', $args);
        $tier = trim($tierFlag, '-');

        if (!$name) {
            echo "\e[31m❌ Error: Usage: php foundry make [Name] [--free|--pro|--global] [--csv]\e[0m\n";
            exit(1);
        }

        // --- THE WIZARD ---
        echo "\e[36m🔗 Dependencies:\e[0m (e.g. geo_context) or Enter for none:\n> ";
        $input = trim(fgets(STDIN));
        $deps = !empty($input) ? array_map('trim', explode(',', $input)) : [];
        $formattedDeps = "['" . implode("', '", $deps) . "']";

        // --- LOGIC: Path & Namespace ---
        $className = ucfirst($name) . "Provider";
        $subDir = ucfirst($tier);
        $namespace = "ApiCrumbs\\Providers\\{$subDir}";
        $directory = getcwd() . "/src/Providers/{$subDir}";
        $filePath = "{$directory}/{$className}.php";

        if (!is_dir($directory)) mkdir($directory, 0755, true);

        if (file_exists($filePath)) {
            echo "\e[33m⚠️  Warning: {$className} already exists at {$filePath}\e[0m\n";
            exit(1);
        }
        
        // --- SELECT STUB ---
        if ($isCsv) {
            $stub = $this->getCsvStub($name, $className, $namespace, $tier, $formattedDeps);
        } else {
            $extendsBase = ($tier !== 'free');
            $stub = $this->getApiStub($name, $className, $namespace, $tier, $formattedDeps, $extendsBase);
        }
        
        if (file_put_contents($filePath, $stub)) {
            echo "\e[32m✨ Created {$subDir} " . ($isCsv ? 'CSV' : 'API') . " Crumb: {$className}\e[0m\n";
            echo "\e[34m📍 Version 1.0.0 | MetadataTransformer included.\e[0m\n";
        }
    }

    private function getApiStub($name, $className, $namespace, $tier, $deps, $extendsBase): string
    {
        $getName = strtolower($name);
        $use = $extendsBase ? "use ApiCrumbs\Core\Contracts\BaseProvider;" : "use ApiCrumbs\Core\Contracts\ProviderInterface;";
        $extends = $extendsBase ? "extends BaseProvider" : "implements ProviderInterface";

        return <<<PHP
<?php

namespace {$namespace};

{$use}

class {$className} {$extends}
{
    public function getName(): string { return '{$getName}_context'; }

    public function getDependencies(): array { return {$deps}; }

    public function getVersion(): string { return '1.0.0'; }

    public function fetchData(string \$id, array \$context = []): array
    {
        // Use \$this->safeFetch() if extending BaseProvider
        return ['id' => \$id, 'status' => 'active'];
    }

    public function transform(array \$data): string
    {
        if (empty(\$data)) return "";
        \$output = "### " . strtoupper(\$this->getName()) . PHP_EOL;
        foreach (\$data as \$k => \$v) { \$output .= "- **" . strtoupper(\$k) . "**: {\$v}" . PHP_EOL; }
        return \$output . "---" . PHP_EOL;
    }
}
PHP;
    }

    private function getCsvStub($name, $className, $namespace, $tier, $deps): string
    {
        $getName = strtolower($name);
        return <<<PHP
<?php

namespace {$namespace};

use ApiCrumbs\Core\Contracts\CsvStreamProvider;

class {$className} extends CsvStreamProvider
{
    public function getName(): string { return '{$getName}_csv_context'; }

    public function getDependencies(): array { return {$deps}; }

    public function getVersion(): string { return '1.0.0'; }

    protected function getSourceUrl(): string 
    {
        return "https://raw.githubusercontent.com{$name}.csv";
    }

    protected function getMapping(): array
    {
        return [
            'id' => 'Original CSV ID Header',
            'value' => 'Original CSV Value Header'
        ];
    }

    public function transform(array \$data): string
    {
        if (empty(\$data)) return "";
        \$output = "### DATA STREAM: " . strtoupper(\$this->getName()) . PHP_EOL;
        foreach (\$data as \$row) {
            \$output .= "- Item: " . (\$row['id'] ?? 'N/A') . PHP_EOL;
        }
        return \$output . "---" . PHP_EOL;
    }
}
PHP;
    }
}