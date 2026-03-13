<?php

namespace ApiCrumbs\Core\Contracts;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * BaseProvider - The Foundation for all API-based Crumbs.
 * Handles XAMPP SSL fixes, Throttling, and Guzzle Orchestration.
 */
abstract class BaseProvider implements ProviderInterface
{
    protected Client $client;
    protected int $throttleMicros = 500000; // 0.5s default delay to respect API limits

    public function __construct(array $customConfig = [])
    {
        // 1. THE XAMPP FIX: Auto-detect local CA bundle from 'foundry setup:ssl'
        $caPath = getcwd() . DIRECTORY_SEPARATOR . 'cacert.pem';
        
        $defaultConfig = [
            'timeout' => 10.0,
            'verify'  => file_exists($caPath) ? $caPath : true,
            'headers' => [
                'User-Agent' => 'ApiCrumbs-Foundry/1.0',
                'Accept'     => 'application/json',
            ]
        ];

        // 2. Initialise the Guzzle Client with merged configs
        $this->client = new Client(array_merge($defaultConfig, $customConfig));
    }

    /**
     * Helper: Throttled GET request for Providers.
     * Prevents "429 Too Many Requests" during complex Stitches.
     */
    protected function safeFetch(string $url, array $options = []): array
    {
        try {
            // Respect the API heartbeat
            usleep($this->throttleMicros);
            
            $response = $this->client->get($url, $options);
            return json_decode($response->getBody()->getContents(), true) ?? [];
            
        } catch (GuzzleException $e) {
            // Log to apicrumbs.log for 'foundry logs' to pick up
            $this->logError($e->getMessage());
            return [];
        }
    }

    private function logError(string $msg): void
    {
        $log = getcwd() . '/apicrumbs.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($log, "[{$timestamp}] 🍪 CRUMB_FAIL: Provider [{$this->getName()}] -> {$msg}" . PHP_EOL, FILE_APPEND);
    }

    /**
     * Default implementation of dependencies. 
     * Specific providers (like Crime) will override this with ['geo_context'].
     */
    public function getDependencies(): array
    {
        return [];
    }
}