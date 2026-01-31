<?php
/**
 * DesbravaHub - Public Entry Point
 * 
 * All requests are routed through this file.
 */

// Load the application bootstrap
require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

// Autoloader for App namespace
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Check if this is a request for the configuration test page
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

// Load and dispatch routes
$router = require BASE_PATH . '/routes/web.php';
$router->dispatch();

/**
 * Configuration test page (temporary)
 */
function showConfigTestPage(): void
{
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars(config('app.name')) ?> - Configuration Test</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                min-height: 100vh;
                color: #e0e0e0;
                padding: 40px 20px;
            }

            .container {
                max-width: 900px;
                margin: 0 auto;
            }

            h1 {
                font-size: 2.5rem;
                margin-bottom: 10px;
                background: linear-gradient(90deg, #00d9ff, #00ff88);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            h2 {
                font-size: 1.2rem;
                color: #888;
                margin-bottom: 30px;
            }

            .card {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 12px;
                padding: 24px;
                margin-bottom: 20px;
                backdrop-filter: blur(10px);
            }

            .card h3 {
                color: #00d9ff;
                margin-bottom: 16px;
                font-size: 1.1rem;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .card h3::before {
                content: '✓';
                background: #00ff88;
                color: #1a1a2e;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 12px;
                font-weight: bold;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            td {
                padding: 8px 0;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            td:first-child {
                color: #888;
                width: 200px;
            }

            td:last-child {
                font-family: 'SF Mono', Monaco, monospace;
                color: #00ff88;
            }

            .badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 20px;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .badge-dev {
                background: #ff6b35;
                color: #fff;
            }

            .badge-staging {
                background: #f7b32b;
                color: #1a1a2e;
            }

            .badge-production {
                background: #00ff88;
                color: #1a1a2e;
            }

            .success {
                color: #00ff88;
            }

            .footer {
                text-align: center;
                margin-top: 40px;
                color: #666;
            }

            .demo-link {
                display: inline-block;
                margin-top: 20px;
                padding: 12px 24px;
                background: linear-gradient(90deg, #00d9ff, #00ff88);
                color: #1a1a2e;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 600;
            }

            .demo-link:hover {
                transform: translateY(-2px);
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>⚡ <?= htmlspecialchars(config('app.name')) ?></h1>
            <h2>Module 3 - Authentication System Ready</h2>

            <div class="card">
                <h3>Application Configuration</h3>
                <table>
                    <tr>
                        <td>Application Name</td>
                        <td><?= htmlspecialchars(config('app.name')) ?></td>
                    </tr>
                    <tr>
                        <td>Base URL</td>
                        <td><?= htmlspecialchars(config('app.base_url')) ?></td>
                    </tr>
                    <tr>
                        <td>Environment</td>
                        <td>
                            <?php
                            $env = config('app.environment');
                            $badgeClass = match ($env) {
                                'dev' => 'badge-dev',
                                'staging' => 'badge-staging',
                                default => 'badge-production'
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= strtoupper($env) ?></span>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="card">
                <h3>Modules Completed</h3>
                <table>
                    <tr>
                        <td>Module 1</td>
                        <td>✓ Environment & Configuration</td>
                    </tr>
                    <tr>
                        <td>Module 2</td>
                        <td>✓ Database Schema (24 tables)</td>
                    </tr>
                    <tr>
                        <td>Module 3</td>
                        <td>✓ Authentication & Authorization</td>
                    </tr>
                </table>
            </div>

            <div class="card">
                <h3>Test Authentication</h3>
                <p style="color: #888; margin-bottom: 15px;">
                    After importing the database schema and seed data, you can test with:
                </p>
                <table>
                    <tr>
                        <td>Demo Tenant URL</td>
                        <td>/demo-club/login</td>
                    </tr>
                    <tr>
                        <td>Admin</td>
                        <td>admin@demo.com / password123</td>
                    </tr>
                    <tr>
                        <td>Director</td>
                        <td>diretor@demo.com / password123</td>
                    </tr>
                    <tr>
                        <td>Pathfinder</td>
                        <td>desbravador@demo.com / password123</td>
                    </tr>
                </table>
                <br>
                <a href="<?= base_url('demo-club/login') ?>" class="demo-link">Test Demo Login →</a>
            </div>

            <p class="footer">
                <span class="success">✓ Modules 1-3 Complete</span>
            </p>
        </div>
    </body>

    </html>
    <?php
}