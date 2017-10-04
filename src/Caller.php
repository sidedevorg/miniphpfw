<?php

namespace SideDevOrg\MiniPhpFw;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;

/**
 * Framework Caller middleware.
 */
class Caller
{
    /**
     * Class name.
     *
     * @var string
     */
    private $class;

    /**
     * Method name.
     *
     * @var string
     */
    private $method;

    /**
     * Method param array.
     *
     * @var array
     */
    private $vars;

    /**
     * Caller middleware construct.
     */
    public function __construct(string $class, string $method, array $vars)
    {
        $this->class = $class;
        $this->method = $method;
        $this->vars = $vars;
    }

    /**
     * Invoke middleware.
     *
     * @param \Psr\Http\Message\RequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param callable                            $next
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, callable $next) : \Psr\Http\Message\ResponseInterface
    {
        $class = new $this->class();
        $class->setRequest($request);

        $response->getBody()->write(
            call_user_func_array([$class, $this->method], $this->vars)
        );

        return $next($request, $response);
    }
}
