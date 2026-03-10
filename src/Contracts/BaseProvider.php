<?php

namespace ApiCrumbs\Core\Contracts;

use GuzzleHttp\Client;

abstract class BaseProvider implements ProviderInterface
{
    protected array $caBundlePaths = [
        'C:\xampp8.2\php\extras\ssl\cacert.pem',
        'C:\xampp\php\extras\ssl\cacert.pem',
    ];

    protected Client $client;

    public function __construct(array $guzzleConfig = [], array $caBundlePaths = [])
    {
        // 1. Detect local SSL certificate for XAMPP/Windows environments
        if ($caBundlePaths) {
            $this->caBundlePaths = $caBundlePaths;
        }
        $caPath = $this->resolveCaBundle();

        // 2. Merge user config with internal safety defaults
        $defaultConfig = [
            'timeout' => 10.0,
            'headers' => ['User-Agent' => 'ApiCrumbs-Foundry/1.0'],
            'verify'  => $caPath ?: true, // Use detected path, otherwise default to system
        ];

        $this->client = new Client(array_merge($defaultConfig, $guzzleConfig));
    }

    /**
     * Finds the cacert.pem in common XAMPP/Windows locations
     */
    private function resolveCaBundle(): ?string
    {
        $paths = $this->caBundlePaths;
        $paths[] = getcwd() . DIRECTORY_SEPARATOR . 'cacert.pem'; // Allow local project-level override

        foreach ($paths as $path) {
            if (file_exists($path)) return $path;
        }

        return null;
    }

    /**
     * Throttled Guzzle Wrapper
     */
    public function safeFetch(string $url, array $options = []): array
    {
        $response = $this->client->get($url, $options);
        return json_decode($response->getBody(), true) ?? [];
    }
}
