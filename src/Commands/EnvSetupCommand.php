<?php

namespace ApiCrumbs\Core\Commands;

/**
 * EnvSetupCommand - Boilerplate Environment Generator
 * Creates the .env file for API keys, Registry tokens, and Driver configs.
 */
class EnvSetupCommand
{
    private string $envFile = '.env';
    private string $exampleFile = '.env.example';

    public function handle(): void
    {
        $target = getcwd() . DIRECTORY_SEPARATOR . $this->envFile;
        $example = getcwd() . DIRECTORY_SEPARATOR . $this->exampleFile;

        echo "📝 \e[1;36mApiCrumbs Environment Setup\e[0m\n";
        echo "-------------------------------\n";

        $template = <<<ENV
# 🍪 APICRUMBS CORE CONFIGURATION
DEBUG_MODE=true
CA_BUNDLE_PATH=cacert.pem

# 🔐 SPONSOWARE REGISTRY TOKEN
# Get yours at: https://github.com
APICRUMBS_PRO_TOKEN=your_sponsor_token_here

# 🧠 AGENT DRIVER CONFIG (Transport)
# Options: openai, anthropic, gemini, ollama
AICRUMBS_LLM_DRIVER=openai
OPENAI_API_KEY=
ANTHROPIC_API_KEY=
GEMINI_API_KEY=
OLLAMA_HOST=http://localhost:11434

# 🍪 BUSINESS PRO: PROVIDER KEYS (Senses)
COMPANIES_HOUSE_KEY=
HMRC_CLIENT_ID=
HMRC_CLIENT_SECRET=

# 🛡️ GLOBAL OPS: SECURITY CONFIG
PII_REDACTION_LEVEL=strict
LOG_AUDIT_TRAIL=true
ENV;

        // 1. Generate .env.example for repository consistency
        file_put_contents($example, $template);
        echo "✅ Generated: {$this->exampleFile}\n";

        // 2. Create .env only if it doesn't already exist (Protection)
        if (file_exists($target)) {
            echo "⚠️  Skipping: '{$this->envFile}' already exists. We won't overwrite your keys.\n";
        } else {
            if (file_put_contents($target, $template)) {
                echo "✅ Created: '{$this->envFile}'\n";
                echo "✨ Success! Open the file to add your API credentials.\n";
            }
        }

        echo "\n\e[33m📍 Tip: Ensure '.env' is added to your .gitignore to prevent leaks!\e[0m\n";
    }
}
