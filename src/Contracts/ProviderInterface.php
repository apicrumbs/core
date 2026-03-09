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
    
    /**
     * @param string $id The primary search term
     * @param array $context Data already collected by previous providers in the stack
     */
    public function fetchData(string $id, array $context = []): array;

    /** Returns metadata about the source (URL, Data Freshness, etc.) */
    public function getMetadata(): array;
}