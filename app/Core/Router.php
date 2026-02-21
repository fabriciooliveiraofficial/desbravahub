<?php
/**
 * Simple Router
 * 
 * Handles URL routing with middleware support.
 */

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];
    private ?string $currentTenant = null;

    /**
     * Add a GET route
     */
    public function get(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Add a POST route
     */
    public function post(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Add a route
     */
    public function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
        return $this;
    }

    /**
     * Add global middleware
     */
    public function middleware(string $middleware): self
    {
        $this->globalMiddleware[] = $middleware;
        return $this;
    }

    /**
     * Dispatch the current request
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Find matching route
        foreach ($this->routes as $route) {
            $params = $this->matchRoute($route['path'], $uri);

            if ($params !== false && $route['method'] === $method) {
                // Run middleware pipeline
                $middleware = array_merge($this->globalMiddleware, $route['middleware']);

                foreach ($middleware as $mw) {
                    $result = $this->runMiddleware($mw, $params);
                    if ($result === false) {
                        return; // Middleware blocked the request
                    }
                }

                // Execute handler
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }

        // No route matched
        $this->notFound();
    }

    /**
     * Match a route pattern against a URI
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        // Convert route pattern to regex
        // {param} becomes a named capture group
        $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            // Extract only named parameters
            return array_filter($matches, fn($k) => !is_numeric($k), ARRAY_FILTER_USE_KEY);
        }

        return false;
    }

    /**
     * Run a middleware
     */
    private function runMiddleware(string $middleware, array $params): bool
    {
        // Check if middleware has parameters (e.g., "permission:activities.create")
        $parts = explode(':', $middleware, 2);
        $class = $parts[0];
        $mwParams = isset($parts[1]) ? explode(',', $parts[1]) : [];

        if (!class_exists($class)) {
            throw new \RuntimeException("Middleware class not found: $class");
        }

        $instance = new $class();
        return $instance->handle($params, $mwParams);
    }

    /**
     * Execute a route handler
     */
    private function executeHandler(callable|array $handler, array $params): void
    {
        if (is_array($handler) && isset($handler[0]) && is_string($handler[0])) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method($params);
        } else {
            // It's a closure
            call_user_func($handler, $params);
        }
    }

    /**
     * Handle 404 Not Found
     */
    private function notFound(): void
    {
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
    }

    /**
     * Set current tenant (for context)
     */
    public function setTenant(string $slug): void
    {
        $this->currentTenant = $slug;
    }

    /**
     * Get current tenant
     */
    public function getTenant(): ?string
    {
        return $this->currentTenant;
    }
}
