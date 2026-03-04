<?php

namespace ApiCrumbs\Core\Contracts;

interface ProviderInterface
{
    public function getName(): string;
    public function fetchData(string $id): array;
}