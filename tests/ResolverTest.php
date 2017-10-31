<?php

use PHPUnit\Framework\TestCase;

/**
 * ResolverTest.
 */
class ResolverTest extends TestCase
{
    /**
     * Test resolver.
     */
    public function testResolver()
    {
        $class = 'DummyController';
        $method = 'dummyMethod';
        $vars = [];

        $request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        $response = new \Zend\Diactoros\Response();

        $middlewares = [];
        $middlewares[] = DummyMiddleware::class;

        $resolver = (new SideDevOrg\MiniPhpFw\Resolver())->dispatch(
            $class,
            $method,
            $vars,
            $request,
            $response,
            $middlewares
        );

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $resolver);
    }
}
