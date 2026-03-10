<?php

namespace ApiCrumbs\Core\Contracts;

interface ProviderInterface
{
    /** The unique key for the context block (e.g., 'company_profile') */
    public function getName(): string;

    /** 
     * Returns an array of provider names this provider depends on.
     * e.g., ['geo_context'] 
     */
    public function getDependencies(): array;
    
    /** The current version of the provider (e.g., '1.0.0') */
    public function getVersion(): string;

    /**
     * @param string $id The primary search term
     * @param array $context Data already collected by previous providers in the stack
     */
    public function fetchData(string $id, array $context = []): array;

    /** 
     * Converts raw array data into LLM-optimized strings.
     * Use this to prune tokens, rename keys, or add system hints.
     */
    public function transform(array $data): string;

    
}