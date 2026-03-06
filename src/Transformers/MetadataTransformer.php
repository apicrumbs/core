<?php

namespace ApiCrumbs\Core\Transformers;

class MetadataTransformer
{
    /**
     * Enhances the markdown with source reliability and timestamps
     */
    public static function wrap(string $providerName, string $markdown, array $meta = []): string
    {
        $timestamp = date('Y-m-d H:i:s T');
        $source = $meta['source_url'] ?? 'Direct API Access';
        $reliability = $meta['reliability'] ?? 'High'; // High, Medium, Low-Volatility

        $header = "### SOURCE: " . strtoupper($providerName) . PHP_EOL;
        $header .= "> [METADATA] Fetched: {$timestamp} | Reliability: {$reliability} | Origin: {$source}" . PHP_EOL . PHP_EOL;

        return $header . $markdown . PHP_EOL . "---" . PHP_EOL;
    }
}