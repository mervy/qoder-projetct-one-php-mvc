<?php

/**
 * Application Entry Point
 * 
 * This is the main entry point for all HTTP requests.
 * It sets up path constants and bootstraps the application.
 */

// Define path constants
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEWS_PATH', BASE_PATH . '/src/Resources/Views');

// Start output buffering
ob_start();

// Bootstrap the application
$app = require_once BASE_PATH . '/bootstrap/app.php';

// Run the application
$app->run();

// Flush output buffer
ob_end_flush();