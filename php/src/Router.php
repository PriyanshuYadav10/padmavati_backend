<?php
/**
 * Minimal router. Supports :placeholder params, e.g. "/api/contacts/:id".
 * Mirrors the express Router wiring in src/routes/*.
 */
class Router
{
    /** @var array<int,array{method:string,regex:string,keys:string[],handler:callable}> */
    private array $routes = [];

    public function add(string $method, string $pattern, callable $handler): void
    {
        $keys = [];
        $regex = preg_replace_callback('#:([A-Za-z_][A-Za-z0-9_]*)#', function ($m) use (&$keys) {
            $keys[] = $m[1];
            return '([^/]+)';
        }, $pattern);
        $regex = '#^' . $regex . '$#';
        $this->routes[] = compact('method', 'regex', 'keys', 'handler');
    }

    public function get(string $p, callable $h): void    { $this->add('GET', $p, $h); }
    public function post(string $p, callable $h): void   { $this->add('POST', $p, $h); }
    public function put(string $p, callable $h): void    { $this->add('PUT', $p, $h); }
    public function delete(string $p, callable $h): void { $this->add('DELETE', $p, $h); }

    public function dispatch(Request $req): void
    {
        $pathMatched = false;

        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $req->path, $m)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $req->method) {
                continue;
            }
            array_shift($m);
            foreach ($route['keys'] as $i => $key) {
                $req->params[$key] = rawurldecode($m[$i] ?? '');
            }
            ($route['handler'])($req);
            return;
        }

        // Path exists but wrong verb → 404 to match the Node notFound behaviour.
        throw ApiException::notFound("Route not found: {$req->method} {$req->path}");
    }
}
