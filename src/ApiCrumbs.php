<?php

namespace ApiCrumbs\Core;

use ApiCrumbs\Core\Contracts\ProviderInterface;
use ApiCrumbs\Core\Transformers\MarkdownTransformer;

class ApiCrumbs
{
    /** @var ProviderInterface[] */
    protected array $providers = [];

    public function registerProvider(ProviderInterface $provider): self
    {
        $this->providers[$provider->getName()] = $provider;
        return $this;
    }

    /**
     * The Magic Method: Loops through all registered providers
     * and returns a single, token-efficient Markdown string.
     */
    public function build(string $id): string
    {
        $output = "";
        foreach ($this->providers as $provider) {
            try {
                $data = $provider->fetchData($id);
                $output .= MarkdownTransformer::toMarkdown($provider->getName(), $data);
            } catch (\Exception $e) {
                // Silently skip or log failed providers to keep the RAG prompt clean
                continue;
            }
        }
        return $output;
    }
}