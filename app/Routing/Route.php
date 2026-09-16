<?php

namespace App\Routing;

/**
 * PlanZeen Phase 1 — lightweight router shim.
 *
 * This mimics Laravel's Route facade syntax (Route::get($uri, $action))
 * on purpose: when this project is migrated to a real Laravel installation
 * in Phase 2, routes/web.php only needs its `use` statement swapped for
 * `Illuminate\Support\Facades\Route` — the route definitions themselves
 * do not need to change.
 */
class Route
{
    /** @var array<int, array{method:string, pattern:string, regex:string, params:array<int,string>, action:mixed, name:?string}> */
    protected static array $routes = [];

    protected static ?string $lastName = null;

    public static function get(string $uri, mixed $action): static
    {
        return static::register('GET', $uri, $action);
    }

    protected static function register(string $method, string $uri, mixed $action): static
    {
        $paramNames = [];
        $pattern = preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function ($m) use (&$paramNames) {
            $paramNames[] = $m[1];
            return '([^/]+)';
        }, $uri);

        $regex = '#^' . rtrim($pattern, '/') . '/?$#u';

        static::$routes[] = [
            'method' => $method,
            'uri' => $uri,
            'regex' => $regex,
            'params' => $paramNames,
            'action' => $action,
            'name' => null,
        ];

        return new static();
    }

    /** Fluent ->name('x') support (not required for dispatch, kept for parity with Laravel syntax). */
    public function name(string $name): static
    {
        $i = count(static::$routes) - 1;
        static::$routes[$i]['name'] = $name;
        return $this;
    }

    /**
     * Dispatch the current request. Returns rendered HTML.
     */
    public static function dispatch(string $method, string $uri): string
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        foreach (static::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['regex'], $path, $matches)) {
                array_shift($matches);
                $params = array_combine($route['params'], $matches);

                return static::invoke($route['action'], $params);
            }
        }

        http_response_code(404);
        return view('errors.404')->render();
    }

    protected static function invoke(mixed $action, array $params): string
    {
        if ($action instanceof \Closure) {
            $result = call_user_func_array($action, $params);
        } elseif (is_array($action) && count($action) === 2) {
            [$class, $methodName] = $action;
            $controller = new $class();
            $result = call_user_func_array([$controller, $methodName], $params);
        } else {
            throw new \RuntimeException('Unsupported route action.');
        }

        if ($result instanceof \App\View\View) {
            return $result->render();
        }

        return (string) $result;
    }

    public static function all(): array
    {
        return static::$routes;
    }
}
