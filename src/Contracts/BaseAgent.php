<?php

namespace ApiCrumbs\Core\Contracts;

/**
 * BaseAgent - The Foundation for all Expert Personas.
 * Handles Driver injection and Payload orchestration.
 */
abstract class BaseAgent implements AgentInterface
{
    protected AgentDriverInterface $driver;

    /**
     * Inject the "Brain" (Driver) into the "Expertise" (Agent)
     */
    public function __construct(AgentDriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * The Execution Loop: 
     * Passes the Agent's Persona + Stitched Context to the Driver.
     */
    public function ask(string $query, string $context): string
    {
        return $this->driver->execute(
            $this->getSystemInstructions(),
            $context,
            $query
        );
    }

    /**
     * Standardised Versioning (Can be overridden by Registry)
     */
    public function getVersion(): string 
    {
        return '1.0.0';
    }

    /**
     * Default Persona Structure:
     * Individual Agents (Auditor, Scout) will override this.
     */
    public function getSystemInstructions(): array
    {
        return [
            'role'      => 'General Assistant',
            'objective' => 'Help the user understand the provided data crumbs.',
            'tone'      => 'Helpful and concise.'
        ];
    }
}