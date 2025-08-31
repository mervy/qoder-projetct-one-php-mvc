<?php

namespace Kurama\Core;

/**
 * Basic Database Class (Placeholder)
 * 
 * This is a minimal database implementation for testing the core container.
 * Full database functionality will be implemented in the next phase.
 */
class Database
{
    /**
     * Database configuration
     * @var array
     */
    private array $config;
    
    /**
     * Create new database instance
     * 
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    /**
     * Get database configuration
     * 
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}