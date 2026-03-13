<?php

namespace ApiCrumbs\Core\Commands;

/**
 * SetupAllCommand - The Master Onboarding Orchestrator
 * Sequences SSL fixes, Environment generation, and System Diagnosis.
 */
class SetupAllCommand
{
    public function handle(): void
    {
        echo "\e[1;36m🍪 ApiCrumbs Full Environment Setup\e[0m\n";
        echo "====================================\n\n";

        // 1. SSL Setup (XAMPP/Windows Fix)
        echo "\e[1mStep 1: SSL Certification\e[0m\n";
        (new SslSetupCommand())->handle();
        echo "\n";

        // 2. Environment Setup (.env Generation)
        echo "\e[1mStep 2: Environment Configuration\e[0m\n";
        (new EnvSetupCommand())->handle();
        echo "\n";

        // 3. System Diagnosis (The Doctor)
        echo "\e[1mStep 3: Final System Diagnosis\e[0m\n";
        (new DoctorCommand())->handle();

        echo "\n\e[32m✨ Onboarding Complete!\e[0m\n";
        echo "🚀 Next: Run '\e[1mphp foundry auth:check\e[0m' to verify your API keys.\n";
    }
}