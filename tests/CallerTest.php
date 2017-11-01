<?php

use PHPUnit\Framework\TestCase;

/**
 * CallerTest.
 */
class CallerTest extends TestCase
{
    /**
     * Test call.
     */
    public function testCall()
    {
        $caller = new SideDevOrg\MiniPhpFw\Caller('DummyController', 'dummyMethod', []);
        $this->assertTrue(is_callable($caller));

        $request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        $response = new \Zend\Diactoros\Response();

        $call = $caller->__invoke(
            $request,
            $response,
            function () use ($response) {
                return $response;
            }
        );

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $call);

        $call->getBody()->rewind();

        $this->assertEquals(
            $call->getBody()->getContents(),
            (new DummyController())->dummyMethod()
        );
    }
}
