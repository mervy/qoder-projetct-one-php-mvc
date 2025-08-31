<?php

namespace Kurama\Core;

use Exception;

/**
 * Main Application Class
 * 
 * This class serves as the main coordinator for the framework,
 * managing the container, configuration, and request lifecycle.
 */
class Application
{
    /**
     * Framework version
     */
    public const VERSION = '1.0.0';
    
    /**
     * The base path of the application
     * @var string
     */
    private string $basePath;
    
    /**
     * The dependency injection container
     * @var Container
     */
    private Container $container;
    
    /**
     * Application configuration
     * @var array
     */
    private array $config = [];
    
    /**
     * Boot status
     * @var bool
     */
    private bool $booted = false;
    
    /**
     * Create new application instance
     * 
     * @param string $basePath The root path of the application
     */
    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/\\');
        $this->container = new Container();
        
        // Register application instance
        $this->container->singleton(self::class, fn() => $this);
        $this->container->singleton('app', fn() => $this);
        $this->container->singleton(Container::class, fn() => $this->container);
    }
    
    /**
     * Get the dependency injection container
     * 
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
    
    /**
     * Get the base path of the application
     * 
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }
    
    /**
     * Get path relative to base path
     * 
     * @param string $path
     * @return string
     */
    public function path(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }
    
    /**
     * Get the config path
     * 
     * @return string
     */
    public function configPath(): string
    {
        return $this->path('config');
    }
    
    /**
     * Get the storage path
     * 
     * @return string
     */
    public function storagePath(): string
    {
        return $this->path('storage');
    }
    
    /**
     * Get the public path
     * 
     * @return string
     */
    public function publicPath(): string
    {
        return $this->path('public');
    }
    
    /**
     * Load configuration files
     * 
     * @return void
     */
    public function loadConfig(): void
    {
        $configFiles = [
            'app' => $this->configPath() . '/app.php',
            'database' => $this->configPath() . '/database.php',
            'view' => $this->configPath() . '/view.php',
        ];
        
        foreach ($configFiles as $name => $file) {
            if (file_exists($file)) {
                $this->config[$name] = require $file;
            }
        }
    }
    
    /**
     * Get configuration value using dot notation
     * 
     * @param string $key Configuration key (e.g., 'app.debug', 'database.default')
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $segment) {
            if (!isset($value[$segment])) {
                return $default;
            }
            $value = $value[$segment];
        }
        
        return $value;
    }
    
    /**
     * Set configuration value
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setConfig(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $segment) {
            if (!isset($config[$segment]) || !is_array($config[$segment])) {
                $config[$segment] = [];
            }
            $config = &$config[$segment];
        }
        
        $config = $value;
    }
    
    /**
     * Check if application is in debug mode
     * 
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->getConfig('app.debug', false);
    }
    
    /**
     * Get application environment
     * 
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->getConfig('app.env', 'production');
    }
    
    /**
     * Boot the application
     * 
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        
        // Load configuration
        $this->loadConfig();
        
        // Set timezone
        $timezone = $this->getConfig('app.timezone', 'UTC');
        date_default_timezone_set($timezone);
        
        // Configure error reporting
        if ($this->isDebug()) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(0);
            ini_set('display_errors', 0);
        }
        
        $this->booted = true;
    }
    
    /**
     * Run the application
     * 
     * @return void
     */
    public function run(): void
    {
        try {
            // Boot the application
            $this->boot();
            
            // Resolve and execute router
            if ($this->container->bound(Router::class)) {
                $router = $this->container->resolve(Router::class);
                $router->execute();
            } else {
                throw new Exception('Router not bound in container');
            }
            
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
    
    /**
     * Handle application exceptions
     * 
     * @param Exception $e
     * @return void
     */
    private function handleException(Exception $e): void
    {
        if ($this->isDebug()) {
            echo "<h1>Application Error</h1>";
            echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
        } else {
            http_response_code(500);
            echo "<h1>500 - Internal Server Error</h1>";
            echo "<p>Something went wrong. Please try again later.</p>";
        }
        
        // Log the error
        error_log($e->getMessage() . "\n" . $e->getTraceAsString());
    }
    
    /**
     * Get framework version
     * 
     * @return string
     */
    public function version(): string
    {
        return self::VERSION;
    }
}