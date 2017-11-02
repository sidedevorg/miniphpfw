<?php

namespace SideDevOrg\MiniPhpFw;

/**
 * Framework bootstrap class.
 */
class Bootstrap
{
    /**
     * Default options.
     *
     * @var array
     */
    private $config = [
        'lang' => 'en',
        'paths' => [
            'database' => 'app/config/database.php',
            'middlewares' => 'app/config/middlewares.php',
            'routes' => 'app/config/routes.php',
            'env' => 'app',
            'view' => 'app/views',
            'i18n' => 'app/langs',
            'routesCache' => 'app/storage',
            'assets_manifest' => 'static/build/mix-manifest.json',
        ],
        'config' => [
            'not_found_controller' => '\SideDevOrg\MiniPhpFw\Controller::not_found',
        ],
    ];

    /**
     * Response.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $response;

    /**
     * Load framework.
     *
     * @param array $config
     *
     * @return \SideDevOrg\MiniPhpFw\Bootstrap
     */
    public function load(array $config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);

        $this->enviroment();
        $this->errors();
        $this->templates();
        $this->orm();
        $this->router();

        return $this;
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
        $dotenv = new \Dotenv\Dotenv($this->config['paths']['env']);
        $dotenv->load();
    }

    /**
     * Load templates strategy.
     */
    private function templates()
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
            $connections = require $this->config['paths']['database'];
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
     * Load router strategy.
     */
    private function router()
    {
        $routes = require $this->config['paths']['routes'];
        $request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();

        $cacheFileName = 'routes.'.filemtime($this->config['paths']['routes']).'.cache';
        $cacheFile = $this->config['paths']['routesCache'].'/'.$cacheFileName;

        if (!file_exists($cacheFile)) {
            array_map('unlink', glob( $this->config['paths']['routesCache'].'/*.cache'));
        }

        $dispatcher = \FastRoute\cachedDispatcher(
            function(\FastRoute\RouteCollector $r) use ($routes) {
                foreach ($routes as $route) {
                    $r->addRoute($route['methods'], $route['endpoint'], $route['call']);
                }
            },
            [
                'cacheFile' => $cacheFile,
            ]
        );

        $request = $request->withHeader('lang', $this->config['lang']);
        $request = $request->withHeader('config', json_encode($this->config));

        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        $response = new \Zend\Diactoros\Response();

        switch ($routeInfo[0]) {

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                $response->getBody()->write('METHOD NOT ALLOWED');
                $response = $response->withStatus(405);
                $this->response = $response;
                break;

            case \FastRoute\Dispatcher::NOT_FOUND:
            case \FastRoute\Dispatcher::FOUND:

                $handler = isset($routeInfo[1]) ? $routeInfo[1] : $this->config['config']['not_found_controller'];
                $vars = isset($routeInfo[2]) ? $routeInfo[2] : [];
                $handler = explode('::', $handler);
                $class = $handler[0];
                $method = $handler[1];

                $response = (new \SideDevOrg\MiniPhpFw\Resolver())->dispatch(
                    $class,
                    $method,
                    $vars,
                    $request,
                    $response,
                    $middlewares = require $this->config['paths']['middlewares']
                );

                if (!isset($routeInfo[1])) {
                    $response = $response->withStatus(404);
                }
                $this->response = $response;
                break;
        }
    }

    /**
     * Get response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse() : \Psr\Http\Message\ResponseInterface
    {
        return $this->response;
    }
}
