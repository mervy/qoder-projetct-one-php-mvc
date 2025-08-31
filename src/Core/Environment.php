<?php

namespace Kurama\Core;

/**
 * Environment Configuration Manager
 * 
 * This class handles loading and parsing of environment variables
 * from .env files and provides a global env() helper function.
 */
class Environment
{
    /**
     * Loaded environment variables
     * @var array
     */
    private array $variables = [];
    
    /**
     * Load environment variables from file
     * 
     * @param string $path Path to .env file
     * @return void
     */
    public function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }
        
        $content = file_get_contents($path);
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $this->parseLine(trim($line));
        }
    }
    
    /**
     * Get environment variable value
     * 
     * @param string $key Variable name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // Check environment variables first
        $envValue = getenv($key);
        if ($envValue !== false) {
            return $this->castValue($envValue);
        }
        
        // Check loaded variables
        if (isset($this->variables[$key])) {
            return $this->castValue($this->variables[$key]);
        }
        
        return $default;
    }
    
    /**
     * Set environment variable
     * 
     * @param string $key Variable name
     * @param mixed $value Variable value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->variables[$key] = $value;
        putenv("{$key}={$value}");
    }
    
    /**
     * Parse a single line from .env file
     * 
     * @param string $line Line to parse
     * @return void
     */
    private function parseLine(string $line): void
    {
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '#') === 0) {
            return;
        }
        
        // Skip lines without assignment operator
        if (strpos($line, '=') === false) {
            return;
        }
        
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes if present
        if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
            $value = $matches[2];
        }
        
        $this->set($key, $value);
    }
    
    /**
     * Cast string values to appropriate types
     * 
     * @param string $value Value to cast
     * @return mixed
     */
    private function castValue($value)
    {
        if ($value === 'true') return true;
        if ($value === 'false') return false;
        if ($value === 'null') return null;
        if ($value === '') return '';
        
        // Check if numeric
        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float)$value : (int)$value;
        }
        
        return $value;
    }
}

/**
 * Global environment helper function
 * 
 * @param string $key Environment variable name
 * @param mixed $default Default value
 * @return mixed
 */
function env(string $key, $default = null)
{
    static $environment = null;
    
    if ($environment === null) {
        $environment = new Environment();
        if (defined('BASE_PATH')) {
            $environment->load(BASE_PATH . '/.env');
        }
    }
    
    return $environment->get($key, $default);
}