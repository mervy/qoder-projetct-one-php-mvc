<?php

namespace Kurama\Core;

/**
 * Basic View Class (Placeholder)
 * 
 * This is a minimal view implementation for testing the core container.
 * Full view functionality will be implemented in the next phase.
 */
class View
{
    /**
     * View configuration
     * @var array
     */
    private array $config;
    
    /**
     * Create new view instance
     * 
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }
    
    /**
     * Render a view (placeholder)
     * 
     * @param string $view
     * @param array $data
     * @return string
     */
    public function render(string $view, array $data = []): string
    {
        return "<p>View system initialized: {$view}</p>";
    }
}