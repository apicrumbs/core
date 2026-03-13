<?php

namespace ApiCrumbs\Core\Contracts;

use ApiCrumbs\Core\Contracts\ProviderInterface;
use Generator;

abstract class CsvStreamProvider implements ProviderInterface
{
    /**
     * Standardised Versioning (Can be overridden by Registry)
     */
    public function getVersion(): string 
    {
        return '1.0.0';
    }
    
    /** 
     * Define header mapping: ['llm_key' => 'CSV Header Name'] 
     */
    abstract protected function getMapping(): array;

    /** 
     * Define the source URL (Local path or GitHub Release URL)
     */
    abstract protected function getSourceUrl(): string;

    /**
     * The Memory-Efficient Engine: Uses Generators for zero-footprint streaming
     */
    protected function stream(string $url): Generator
    {
        $handle = @fopen($url, 'r');
        
        if (!$handle) {
            throw new \Exception("Failed to open CSV stream: {$url}");
        }

        $headers = null;
        $mapping = $this->getMapping();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                if (!$headers) {
                    $headers = array_map('trim', $data);
                    continue;
                }

                $row = array_combine($headers, $data);
                
                // Canonicalize: Map messy CSV keys to Standard LLM Keys
                $cleanRow = [];
                foreach ($mapping as $standardKey => $csvKey) {
                    $cleanRow[$standardKey] = $row[$csvKey] ?? 'N/A';
                }

                yield $cleanRow;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Default implementation: Returns a slice to prevent LLM context bloat
     */
    public function fetchData(string $id, array $context = []): array
    {
        $results = [];
        $limit = 15; // LLM Safety Limit

        // Note: $id could be used here to filter the CSV stream (e.g. by Department)
        foreach ($this->stream($this->getSourceUrl()) as $row) {
            $results[] = $row;
            if (count($results) >= $limit) break;
        }

        return $results;
    }
}
