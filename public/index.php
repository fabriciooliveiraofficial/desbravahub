<?php
/**
 * DesbravaHub Front Controller
 */

// Load the bootstrap file
require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

// Dispatch the router
$router = require BASE_PATH . '/routes/web.php';
$router->dispatch();
