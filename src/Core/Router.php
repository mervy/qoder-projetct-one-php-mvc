<?php

namespace Kurama\Core;

/**
 * Basic Router Class (Placeholder)
 * 
 * This is a minimal router implementation for testing the core container.
 * Full router functionality will be implemented in the next phase.
 */
class Router
{
    /**
     * Dependency injection container
     * @var Container
     */
    private Container $container;
    
    /**
     * Create new router instance
     * 
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Execute router (placeholder)
     * 
     * @return void
     */
    public function execute(): void
    {
        // For now, just display a simple message to test the container
        echo "<h1>Qoder PHP MVC Framework</h1>";
        echo "<p>Core container is working! 🎉</p>";
        echo "<p>Framework version: " . Application::VERSION . "</p>";
        echo "<p>Current environment: " . env('APP_ENV', 'production') . "</p>";
        echo "<p>Debug mode: " . (env('APP_DEBUG', false) ? 'Enabled' : 'Disabled') . "</p>";
    }
}