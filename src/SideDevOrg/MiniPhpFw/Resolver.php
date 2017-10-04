<?php

namespace SideDevOrg\MiniPhpFw;

use Relay\RelayBuilder;

/**
 * Framework Resolver.
 */
class Resolver
{
    /**
     * Resolver dispatch.
     *
     * @param string                              $class
     * @param string                              $method
     * @param array                               $vars
     * @param \Psr\Http\Message\RequestInterface  $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array                               $middlewares
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(
        string $class,
        string $method,
        array $vars,
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $middlewares
    ) : \Psr\Http\Message\ResponseInterface {
        $middlewares[] = Caller::class;

        $relayBuilder = new RelayBuilder(function ($instance) use ($class, $method, $vars) {
            if ($instance == Caller::class) {
                return new $instance($class, $method, $vars);
            }

            return new $instance();
        });

        $relay = $relayBuilder->newInstance($middlewares);

        return $relay($request, $response);
    }
}
