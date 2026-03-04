<?php

namespace ApiCrumbs\Core;

use ApiCrumbs\Core\Contracts\ProviderInterface;
use ApiCrumbs\Core\Transformers\MarkdownTransformer;

class ApiCrumbs
{
    protected array $providers = [];

    public function registerProvider(ProviderInterface $provider): self
    {
        $this->providers[$provider->getName()] = $provider;
        return $this;
    }

    public function build(string $id): string
    {
        $output = "";
        foreach ($this->providers as $provider) {
            $data = $provider->fetchData($id);
            $output .= MarkdownTransformer::toMarkdown($provider->getName(), $data);
        }
        return $output;
    }
}
