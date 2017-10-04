<?php

namespace SideDevOrg\MiniPhpFw;

/**
 * Framework bootstrap class.
 */
class Bootstrap
{
    /**
     * Default paths.
     *
     * @var array
     */
    private $paths = [
        'database' => 'app/config/database.php',
        'middlewares' => 'app/config/middlewares.php',
        'routes' => 'app/config/routes.php',
        'env' => 'app',
        'view' => 'app/views',
        'i18n' => 'app/langs',
    ];

    /**
     * Default options.
     *
     * @var array
     */
    private $options = [
        'routesCache' => 'app/storage/route.cache',
    ];

    /**
     * Load framework.
     *
     * @param array $paths
     * @param array $options
     */
    public function load(array $paths = [], array $options = [])
    {
        $this->paths = array_merge($this->paths, $paths);
        $this->options = array_merge($this->options, $options);

        $this->errors();
        $this->enviroment();
        $this->templates();
        $this->orm();
        $this->route();
    }

    /**
     * Load errors strategy.
     */
    private function errors()
    {
        if (env('DEV_MODE')) {
            $whoops = new \Whoops\Run();
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
            $whoops->register();
        }
    }

    /**
     * Load enviroment strategy.
     */
    private function enviroment()
    {
        $dotenv = new \Dotenv\Dotenv($this->paths['env']);
        $dotenv->load();
    }

    /**
     * Load templates strategy.
     */
    private function templates(): void
    {
        \Mustache_Autoloader::register();
    }

    /**
     * Load orm strategy.
     */
    private function orm()
    {
        if (env('LOAD_ORM')) {
            $capsule = new \Illuminate\Database\Capsule\Manager();
            $connections = require_once $this->paths['database'];
            foreach ($connections as $connection) {
                $name = $connection['name'];
                unset($connection['name']);
                $capsule->addConnection($connection, $name);
            }
            $capsule->setEventDispatcher(
                new \Illuminate\Events\Dispatcher(
                    new \Illuminate\Container\Container()
                )
            );
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
        }
    }

    /**
     * Load route strategy.
     */
    private function route()
    {
        $routes = require_once $this->paths['routes'];
        $request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();

        $dispatcher = \FastRoute\cachedDispatcher(
            function (\FastRoute\RouteCollector $r) use ($routes) {
                foreach ($routes as $route) {
                    $r->addRoute($route['methods'], $route['endpoint'], $route['call']);
                }
            }, [
                'cacheFile' => $this->options['routesCache'],
            ]
        );

        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        switch ($routeInfo[0]) {

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                echo '405 METHOD_NOT_ALLOWED';
                break;

            case \FastRoute\Dispatcher::NOT_FOUND:
            case \FastRoute\Dispatcher::FOUND:

                $handler = isset($routeInfo[1]) ? $routeInfo[1] : '\SideDevOrg\MiniPhpFw\Controller::not_found';
                $vars = isset($routeInfo[2]) ? $routeInfo[2] : [];
                $handler = explode('::', $handler);
                $class = $handler[0];
                $method = $handler[1];

                $response = (new \SideDevOrg\MiniPhpFw\Resolver())->dispatch(
                    $class,
                    $method,
                    $vars,
                    $request,
                    $response = new \Zend\Diactoros\Response(),
                    $middlewares = require_once $this->paths['middlewares']
                );

                (new \Zend\Diactoros\Response\SapiEmitter())->emit($response);
                break;
        }
    }
}
