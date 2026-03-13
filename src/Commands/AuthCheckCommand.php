<?php

namespace ApiCrumbs\Core\Commands;

use GuzzleHttp\Client;

/**
 * AuthCheckCommand - Real-time Credential Validator
 * Pings Provider and Driver endpoints to ensure API keys are active.
 */
class AuthCheckCommand
{
    public function handle(): void
    {
        echo "🔐 \e[1;36mApiCrumbs Credential Validator\e[0m\n";
        echo "----------------------------------\n";

        $this->checkCompaniesHouse();
        $this->checkHmrc();
        $this->checkLlmDriver();
        
        echo "\n\e[36m💡 Tip: If checks fail, verify your .env file or API dashboard permissions.\e[0m\n";
    }

    private function checkCompaniesHouse(): void
    {
        $key = getenv('COMPANIES_HOUSE_KEY');
        echo "🏢 Companies House: ";

        if (!$key || $key === 'your_key_here') {
            echo "\e[33mMISSING\e[0m (Check .env)\n";
            return;
        }

        $client = new Client(['http_errors' => false]);
        // Companies House uses the key as the Username in Basic Auth
        $response = $client->get('https://api.company-information.service.gov.uk', [
            'auth' => [$key, ''],
            'query' => ['q' => 'TESCO', 'items_per_page' => 1]
        ]);

        echo ($response->getStatusCode() === 200) ? "\e[32mACTIVE\e[0m\n" : "\e[31mINVALID (Status: " . $response->getStatusCode() . ")\e[0m\n";
    }

    private function checkHmrc(): void
    {
        $id = getenv('HMRC_CLIENT_ID');
        echo "🏛️  HMRC API: ";

        if (!$id) {
            echo "\e[33mSKIPPED\e[0m (No ID found)\n";
            return;
        }

        $client = new Client(['http_errors' => false]);
        // Pinging the HMRC 'Hello World' public endpoint
        $response = $client->get('https://test-api.service.hmrc.gov.uk', [
            'headers' => ['Accept' => 'application/vnd.hmrc.1.0+json']
        ]);

        echo ($response->getStatusCode() === 200) ? "\e[32mREACHABLE\e[0m\n" : "\e[31mUNREACHABLE\e[0m\n";
    }

    private function checkLlmDriver(): void
    {
        $driver = getenv('AICRUMBS_LLM_DRIVER') ?: 'openai';
        $key = ($driver === 'openai') ? getenv('OPENAI_API_KEY') : getenv('GEMINI_API_KEY');
        
        echo "🧠 Driver [{$driver}]: ";

        if (!$key) {
            echo "\e[31mKEY MISSING\e[0m\n";
            return;
        }

        $client = new Client(['http_errors' => false]);
        
        // Lightweight model list check to verify key without spending tokens
        $url = ($driver === 'openai') ? 'https://api.openai.com' : 'https://generativelanguage.googleapis.com' . $key;
        $headers = ($driver === 'openai') ? ['Authorization' => "Bearer {$key}"] : [];

        $response = $client->get($url, ['headers' => $headers]);

        echo ($response->getStatusCode() === 200) ? "\e[32mAUTHENTICATED\e[0m\n" : "\e[31mAUTH FAILED\e[0m\n";
    }
}
