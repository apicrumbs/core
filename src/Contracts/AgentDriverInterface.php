<?php

namespace ApiCrumbs\Core\Contracts;

interface AgentDriverInterface
{
    /**
     * @param array $instructions The Agent's persona (role, objective, tone)
     * @param string $context The stitched Markdown data
     * @param string $query The user's specific question
     */
    public function execute(array $instructions, string $context, string $query): string;
}