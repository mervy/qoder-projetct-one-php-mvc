<?php

/**
 * Application Bootstrap
 * 
 * This file initializes the application, loads dependencies,
 * and configures the core services.
 */

require_once BASE_PATH . '/vendor/autoload.php';

use Kurama\Core\Application;
use Kurama\Core\Environment;
use Kurama\Core\Container;
use Kurama\Core\Database;
use Kurama\Core\Router;
use Kurama\Core\View;

// Load environment variables first to make env() function available
$env = new Environment();
$env->load(BASE_PATH . '/.env');

// Make env() function globally available
if (!function_exists('env')) {
    function env(string $key, $default = null) {
        global $env;
        return $env->get($key, $default);
    }
}

// Create application instance
$app = new Application(BASE_PATH);

// Get container for service registration
$container = $app->getContainer();

// Register core services
$container->singleton(Database::class, function() use ($app) {
    $config = $app->getConfig('database');
    if (!$config) {
        throw new Exception('Database configuration not found');
    }
    $database = new Database($config['connections'][$config['default']]);
    
    // Set database instance for Model class
    \Kurama\Core\Model::setDatabase($database);
    
    return $database;
});

$container->singleton(View::class, function() use ($app) {
    $config = $app->getConfig('view', [
        'paths' => [BASE_PATH . '/src/Resources/Views'],
        'cache' => BASE_PATH . '/storage/views',
        'debug' => $app->isDebug()
    ]);
    return new View($config);
});

$container->singleton(Router::class, function($container) {
    return new Router($container);
});

// Register environment as singleton
$container->singleton(Environment::class, fn() => $env);

// Load application configuration
$app->loadConfig();

// Load routes
$routesFile = BASE_PATH . '/routes/web.php';
if (file_exists($routesFile)) {
    require_once $routesFile;
}

// Return the application instance
return $app;