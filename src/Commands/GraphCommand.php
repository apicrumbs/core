<?php

namespace ApiCrumbs\Core\Commands;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * GraphCommand - Full Ecosystem Visualiser
 * Scans all pillars and draws the "Stitch" map.
 */
class GraphCommand
{
    public function handle(): void
    {
        echo "\e[1;36m📊 ApiCrumbs Ecosystem Graph\e[0m\n";
        echo "============================\n\n";

        // 1. The Transports (Drivers)
        echo "\e[1;32m🔌 DRIVERS (Transports)\e[0m\n";
        foreach ($this->scan('Core/Drivers') as $d) {
            $active = (getenv('AICRUMBS_LLM_DRIVER') === $d) ? " \e[32m(ACTIVE)\e[0m" : "";
            echo "├── {$d}{$active}\n";
        }
        echo "└── \e[2m[Ready to carry context]\e[0m\n\n";

        // 2. The Brains (Agents)
        echo "\e[1;35m🧠 AGENTS (Expertise)\e[0m\n";
        foreach ($this->scan('Agents') as $a) {
            echo "├── {$a}\n";
            // In a full implementation, we'd instantiate to get getRequiredCrumbs()
        }
        echo "└── \e[2m[Reasoning modules]\e[0m\n\n";

        // 3. The Senses (Providers)
        echo "\e[1;34m🍪 PROVIDERS (Crumbs)\e[0m\n";
        foreach ($this->scan('Providers') as $p) {
            echo "├── {$p}\n";
        }
        echo "└── \e[2m[Raw data sources]\e[0m\n";

        echo "\n\e[36m💡 Run 'foundry trace [ID]' to see a specific data-flow path.\e[0m\n";
    }

    private function scan(string $subDir): array
    {
        $dir = getcwd() . '/src/' . $subDir;
        if (!is_dir($dir)) return ["(Empty)"];

        $items = [];
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($files as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') continue;
            
            // Format name: FiscalAuditorAgent.php -> fiscal_auditor
            $name = str_replace(['Agent', 'Provider', 'Driver', '.php'], '', $file->getFilename());
            $items[] = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        }
        return array_unique($items);
    }
}