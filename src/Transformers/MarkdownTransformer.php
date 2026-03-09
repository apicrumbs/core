<?php

namespace ApiCrumbs\Core\Transformers;

class MarkdownTransformer
{
    public static function toMarkdown(string $label, array $data): string
    {
        if (empty($data)) return "";

        $md = "### " . strtoupper($label) . PHP_EOL;
        foreach ($data as $key => $val) {
            $key = str_replace('_', ' ', strtoupper($key));
            $md .= "- **{$key}**: {$val}" . PHP_EOL;
        }
        return $md;// . "---" . PHP_EOL;
    }
}