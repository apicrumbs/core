<?php

namespace ApiCrumbs\Core;

use ApiCrumbs\Core\Contracts\ProviderInterface;
use ApiCrumbs\Core\Transformers\MarkdownTransformer;
use ApiCrumbs\Core\Transformers\MetadataTransformer;

class ApiCrumbs
{
    /** @var ProviderInterface[] */
    protected array $providers = [];

    /**
     * Fluent Registration: Allows chaining like:
     * $api->registerProvider(new PostcodeProvider())
     *     ->registerProvider(new OpenMeteoProvider($customGuzzle));
     */
    public function registerProvider(ProviderInterface $provider): self
    {
        // Use the provider's internal name as the unique key
        $this->providers[$provider->getName()] = $provider;
        return $this;
    }

    /**
     * The Magic Method: Orchestrates all registered providers into 
     * a single, token-efficient Markdown string for LLM injection.
     */
    public function build(string $id): string
    {
        $output = "# KNOWLEDGE CONTEXT: " . strtoupper($id) . PHP_EOL;
        $output .= "Context Generated: " . gmdate('Y-m-d H:i:s') . " UTC" . PHP_EOL . PHP_EOL;
        $masterContext = []; // The "Memory" for the current run
        
        // Magic: Auto-sort providers by their dependency graph
        $executionStack = Resolver::sort($this->providers);

        foreach ($executionStack as $provider) {
            try {
                // 1. Fetch data from the API via the Provider's internal Guzzle client
                $data = $provider->fetchData($id, $masterContext);
                
                if (empty($data)) {
                    throw new \Exception("No data found for ID: ". $id);
                    continue;
                }
                
                // Merge results into context so the NEXT provider can use it
                $masterContext[$provider->getName()] = $data;

                // The Provider dictates its own Markdown structure
                $output .= $provider->transform($data); 

            } catch (\Exception $e) {
                // Log the error internally for debugging
                $this->logError($provider->getName(), $e->getMessage());
                continue;
            }
        }

        return trim($output);
    }

    /**
     * Internal logging logic (Sponsoware Grade)
     */
    private function logError(string $providerName, string $message): void
    {
        $logPath = getcwd() . '/apicrumbs.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] 🍪 CRUMB_FAIL: Provider [{$providerName}] -> {$message}" . PHP_EOL;
        
        file_put_contents($logPath, $logEntry, FILE_APPEND);
    }
}