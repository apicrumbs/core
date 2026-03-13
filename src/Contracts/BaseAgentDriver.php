<?php

namespace ApiCrumbs\Core\Contracts;

use GuzzleHttp\Client;

/**
 * BaseAgentDriver - The Foundation for all LLM Transports.
 * Provides shared Guzzle logic and error handling for the Registry.
 */
abstract class BaseAgentDriver implements AgentDriverInterface
{
    protected Client $client;

    public function __construct(array $config = [])
    {
        // 1. THE XAMPP FIX: Auto-detect local CA bundle from 'foundry setup:ssl'
        $caPath = getcwd() . DIRECTORY_SEPARATOR . 'cacert.pem';
        
        $this->client = new Client(array_merge([
            'timeout' => 30.0,
            'verify'  => file_exists($caPath) ? $caPath : true,
            'headers' => ['Content-Type' => 'application/json']
        ], $config));
    }

    /**
     * Common Response Parser:
     * Individual Drivers will override this to pluck the text from 
     * their specific JSON structure (e.g. OpenAI 'choices' vs Gemini 'candidates').
     */
    abstract protected function parseResponse(array $data): string;

    /**
     * Telemetry & Audit:
     * Records the token usage or latency to apicrumbs.log
     */
    protected function logUsage(string $driver, int $chars): void
    {
        $tokens = ceil($chars / 4);
        $log = getcwd() . '/apicrumbs.log';
        file_put_contents($log, "[".date('Y-m-d H:i:s')."] 🔌 DRIVER_USE: [{$driver}] ~{$tokens} tokens sent.\n", FILE_APPEND);
    }
}