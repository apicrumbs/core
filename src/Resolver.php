<?php

namespace ApiCrumbs\Core;

class Resolver
{
    public static function sort(array $providers): array
    {
        $sorted = [];
        $visited = [];

        $visit = function ($name) use (&$visit, &$providers, &$sorted, &$visited) {
            if (isset($visited[$name])) return;
            $visited[$name] = true;

            foreach ($providers[$name]->getDependencies() as $dep) {
                if (isset($providers[$dep])) {
                    $visit($dep);
                }
            }
            $sorted[] = $providers[$name];
        };

        foreach (array_keys($providers) as $name) {
            $visit($name);
        }

        return $sorted;
    }
}