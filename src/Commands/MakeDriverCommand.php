<?php

namespace ApiCrumbs\Core\Commands;

/**
 * MakeDriverCommand - LLM Transport Scaffolder
 * Generates new Driver classes for the Registry.
 */
class MakeDriverCommand
{
    public function handle(array $args): void
    {
        $name = $args[2] ?? null;
        if (!$name) {
            echo "\e[31m❌ Error: Usage: php foundry make:driver [Name] [--pro]\e[0m\n";
            return;
        }

        $isPro = in_array('--pro', $args);
        $tier = $isPro ? 'Pro' : 'Free';
        $className = ucfirst($name) . "Driver";
        
        // Drivers live in /Drivers to maintain the Transport layer
        $directory = getcwd() . "/src/Drivers/" . $tier;
        $namespace = "ApiCrumbs\\Drivers\\{$tier}";
        $filePath = "{$directory}/{$className}.php";

        if (!is_dir($directory)) mkdir($directory, 0755, true);

        $stub = $this->getDriverStub($namespace, $className, $name);

        if (file_put_contents($filePath, $stub)) {
            echo "\e[32m✨ Created {$tier} Driver: {$className}\e[0m\n";
            echo "📂 Path: {$filePath}\n";
            echo "💡 Next: Define the API endpoint and parseResponse logic.\n";
        }
    }

    private function getDriverStub($ns, $class, $rawName): string
    {
        return <<<PHP
<?php

namespace {$ns};

use ApiCrumbs\Core\Contracts\BaseAgentDriver;

/**
 * {$class} - Custom LLM Transport
 */
class {$class} extends BaseAgentDriver
{
    public function getVersion(): string { return '1.0.0'; }
    
    public function execute(array \$inst, string \$context, string \$query): string
    {
        // 1. Log usage for the Registry Stats
        \$this->logUsage('{$rawName}', strlen(\$context));

        // 2. Prepare your specific API Payload
        \$response = \$this->client->post('https://api.example.com', [
            'headers' => [
                'Authorization' => 'Bearer ' . getenv('" . strtoupper($rawName) . "_API_KEY'),
            ],
            'json' => [
                'model' => 'default-model',
                'messages' => [
                    ['role' => 'system', 'content' => \$inst['role']],
                    ['role' => 'user', 'content' => "Context:\\n" . \$context . "\\n\\nQuery: " . \$query]
                ]
            ]
        ]);

        return \$this->parseResponse(json_decode(\$response->getBody(), true));
    }

    protected function parseResponse(array \$data): string
    {
        // TODO: Pluck the text from the specific API response JSON
        return \$data['answer'] ?? 'No response content.';
    }
}
PHP;
    }
}