<?php

namespace ApiCrumbs\Core\Commands;

class MakeAgentCommand
{
    public function handle(array $args): void
    {
        $name = $args[2] ?? null;
        $tierFlag = $args[3] ?? '--free';
        $tier = trim($tierFlag, '-');

        if (!$name) {
            echo "\e[31m❌ Error: Usage: php foundry make:agent [Name] [--free|--pro]\e[0m\n";
            exit(1);
        }

        // --- THE AGENT WIZARD ---
        echo "\e[35m🧠 Agent Persona:\e[0m (e.g. Fiscal Auditor, Market Scout)\n> ";
        $persona = trim(fgets(STDIN));

        echo "\e[36m🍪 Required Crumbs:\e[0m (Comma separated IDs, e.g. geo_context, companies_house)\n> ";
        $input = trim(fgets(STDIN));
        $crumbs = !empty($input) ? array_map('trim', explode(',', $input)) : [];
        $formattedCrumbs = "['" . implode("', '", $crumbs) . "']";

        // --- FILE GENERATION ---
        $className = ucfirst($name) . "Agent";
        $subDir = ucfirst($tier);
        $namespace = "ApiCrumbs\\Agents\\{$subDir}";
        $directory = getcwd() . "/src/Agents/{$subDir}";
        $filePath = "{$directory}/{$className}.php";

        if (!is_dir($directory)) mkdir($directory, 0755, true);

        $stub = $this->getStub($name, $className, $namespace, $tier, $persona, $formattedCrumbs);
        
        if (file_put_contents($filePath, $stub)) {
            echo "\e[32m✨ Created {$subDir} Agent: {$className}\e[0m\n";
            echo "\e[34m📍 Required Data: " . ($input ?: 'None') . "\e[0m\n";
        }
    }

    private function getStub($name, $className, $namespace, $tier, $persona, $crumbs): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use ApiCrumbs\Core\Contracts\BaseAgent;

/**
 * ApiCrumbs {$tier} Agent: {$name}
 * Role: {$persona}
 */
class {$className} extends BaseAgent
{
    public function getName(): string { return '{$name}'; }

    public function getVersion(): string { return '1.0.0'; }

    public function getRequiredCrumbs(): array 
    { 
        return {$crumbs}; 
    }

    public function getSystemInstructions(): array 
    {
        return [
            'role'      => '{$persona}',
            'objective' => 'Analyze provided context for high-signal insights.',
            'tone'      => 'Professional and analytical.'
        ];
    }
}
PHP;
    }
}