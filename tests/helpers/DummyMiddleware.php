<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;

/**
 * Dummy middleware.
 */
class DummyMiddleware
{
    /**
     * Middleware construct.
     */
    public function __construct()
    {
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
        return $next($request, $response);
    }
}
