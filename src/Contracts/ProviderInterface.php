<?php

namespace ApiCrumbs\Core\Contracts;

interface ProviderInterface
{
    /** The unique key for the context block (e.g., 'company_profile') */
    public function getName(): string;

    /** Fetch and return high-signal data as an associative array */
    public function fetchData(string $id): array;

    /** Returns metadata about the source (URL, Data Freshness, etc.) */
    public function getMetadata(): array;
}