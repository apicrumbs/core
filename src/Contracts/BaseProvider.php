<?php

namespace ApiCrumbs\Core\Contracts;

use GuzzleHttp\Client;

abstract class BaseProvider implements ProviderInterface
{
    protected Client $client;

    public function __construct(array $guzzleConfig = [])
    {
        $this->client = new Client(array_merge([
            'timeout' => 10.0,
            'headers' => ['User-Agent' => 'ApiCrumbs-Foundry/1.0']
        ], $guzzleConfig));
    }

    /**
     * Wrapped fetch
     */
    public function safeFetch(string $url, array $options = []): array
    {
        $response = $this->client->get($url, $options);
        return json_decode($response->getBody(), true) ?? [];
    }   
}
