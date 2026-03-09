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

        foreach ($this->providers as $provider) {
            try {
                // 1. Fetch data from the API via the Provider's internal Guzzle client
                $data = $provider->fetchData($id);
                
                if (empty($data)) continue;
                
                // 2. Transform the raw array to high-signal Markdown
                $markdown = (new MarkdownTransformer())->toMarkdown(
                    $provider->getName(), 
                    $data
                );

                // 3. Ground the data with Metadata (Source, Reliability, Tier)
                $output .= MetadataTransformer::wrap(
                    $provider->getName(),
                    $markdown,
                    $provider->getMetadata()
                );

            } catch (\Exception $e) {
                // Fail silently to keep the LLM context clean of PHP errors
                // Log $e->getMessage() to your internal logger here
                continue;
            }
        }

        return trim($output);
    }
}