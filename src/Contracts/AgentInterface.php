<?php

namespace ApiCrumbs\Core\Contracts;

/**
 * AgentInterface - The "Intelligence" Contract
 * Defines how an Expert Persona requests data and reasons with it.
 */
interface AgentInterface
{
    /**
     * Unique identifier for the agent (e.g., 'fiscal_auditor', 'local_guide').
     */
    public function getName(): string;

    /**
     * Version string for Registry syncing (e.g., '1.0.5').
     */
    public function getVersion(): string;

    /**
     * The "Data Manifest": Returns an array of Provider IDs this agent 
     * needs to "summon" to perform its job.
     * Example: ['geo_context', 'hmrc_spending']
     */
    public function getRequiredCrumbs(): array;

    /**
     * The Persona: Returns the System Instructions for the LLM.
     * Includes 'role', 'objective', and 'tone'.
     */
    public function getSystemInstructions(): array;

    /**
     * The Reasoning Engine: Sends the stitched Markdown and Query 
     * to the assigned Driver (OpenAI, Claude, etc.)
     */
    public function ask(string $query, string $context): string;
}