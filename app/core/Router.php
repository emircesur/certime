<?php

namespace App\Core;

class Router {
    protected $routes = [
        'GET' => [],
        'POST' => []
    ];

    public static function load($file)
    {
        $router = new static;
        require $file;
        return $router;
    }

    public function get($uri, $controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    public function direct($uri, $requestType)
    {
        if (!isset($this->routes[$requestType])) {
            http_response_code(405);
            require APP_PATH . '/views/errors/404.php';
            exit();
        }

        foreach ($this->routes[$requestType] as $route => $controller) {
            $pattern = preg_replace('#:([\w]+)#', '([^/]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$ctrl, $act] = explode('@', $controller);
                return $this->callAction($ctrl, $act, $matches);
            }
        }
        
        http_response_code(404);
        $title = 'Page Not Found';
        require APP_PATH . '/views/errors/404.php';
        exit();
    }

    protected function callAction($controller, $action, $params = [])
    {
        $className = 'App\\Controllers\\' . $controller;
        
        if (!class_exists($className)) {
            throw new \Exception("Controller {$className} not found.");
        }

        $controllerInstance = new $className;

        if (!method_exists($controllerInstance, $action)) {
            throw new \Exception("{$className} does not respond to the {$action} action.");
        }

        return $controllerInstance->$action(...$params);
    }
}
