<?php
/**
 * DesbravaHub Front Controller
 */

// Start output buffering to catch any early notices/warnings
ob_start();

// Load the bootstrap file
require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

// Dispatch the router
$router = require BASE_PATH . '/routes/web.php';
$router->dispatch();
