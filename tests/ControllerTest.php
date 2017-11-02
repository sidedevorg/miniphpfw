<?php

use PHPUnit\Framework\TestCase;

/**
 * ControllerTest.
 */
class ControllerTest extends TestCase
{
    /**
     * Config.
     *
     * @var array
     */
    private $config = [
        'lang' => 'en',
        'paths' => [
            'database' => 'tests/helpers/files/database.php',
            'middlewares' => 'tests/helpers/files/middlewares.php',
            'routes' => 'tests/helpers/files/routes.php',
            'env' => 'tests/helpers/files',
            'view' => 'tests/helpers/files/views',
            'i18n' => 'tests/helpers/files/langs',
            'routesCache' => 'tests/helpers/files/route.cache',
            'assets_manifest' => 'tests/helpers/files/mix-manifest.json',
        ],
        'config' => [
            'not_found_controller' => 'DummyController::not_found',
        ],
    ];

    /**
     * Controller.
     *
     * @var \SideDevOrg\MiniPhpFw\Controller
     */
    private $controller;

    /**
     * Request.
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    private $request;

    /**
     * Set up.
     */
    public function setUp()
    {
        $this->request = \Zend\Diactoros\ServerRequestFactory::fromGlobals();
        $this->request = $this->request->withHeader('config', json_encode($this->config));

        $this->controller = new DummyController();
    }

    /**
     * Test set request.
     */
    public function testSetRequest()
    {
        $request = $this->controller->setRequest($this->request);

        $this->assertInstanceOf('\Psr\Http\Message\RequestInterface', $request);
    }

    /**
     * Test get header.
     */
    public function testGetHeader()
    {
        $class = new ReflectionClass('DummyController');
        $method = $class->getMethod('header');
        $method->setAccessible(true);

        $this->controller->setRequest($this->request);

        $header = $method->invokeArgs($this->controller, ['config']);
        $this->assertEquals(is_string($header), true);

        $header = $method->invokeArgs($this->controller, ['non-exists-header']);
        $this->assertEquals($header, null);
    }

    /**
     * Test get input.
     */
    public function testInput()
    {
        $class = new ReflectionClass('DummyController');
        $method = $class->getMethod('input');
        $method->setAccessible(true);

        $this->request= $this->request
            ->withMethod('POST')
            ->withParsedBody(['input_key'=>'is_ok'])
        ;
        $this->controller->setRequest($this->request);

        $input = $method->invokeArgs($this->controller, ['input_key']);
        $this->assertEquals($input, 'is_ok');

        $input = $method->invokeArgs($this->controller, ['non-exists', ['var' => false] ]);
        $this->assertEquals(is_array($input), true);

        $this->request= $this->request
            ->withMethod('GET')
            ->withQueryParams(['input_key'=>'is_ok'])
        ;
        $this->controller->setRequest($this->request);

        $input = $method->invokeArgs($this->controller, ['input_key']);
        $this->assertEquals($input, 'is_ok');

        $input = $method->invokeArgs($this->controller, ['non-exists', false]);
        $this->assertEquals($input, false);
    }

    /**
     * Test set and get data.
     */
    public function testData()
    {
        $class = new ReflectionClass('DummyController');

        $methodSet = $class->getMethod('data');
        $methodSet->setAccessible(true);

        $methodGet = $class->getMethod('getData');
        $methodGet->setAccessible(true);

        $data = $methodGet->invoke($this->controller);
        $this->assertEquals($data, []);

        $data = $methodSet->invokeArgs($this->controller, ['key']);
        $this->assertEquals($data, false);

        $data = $methodSet->invokeArgs($this->controller, ['key', 'value']);
        $this->assertEquals($data, 'value');

        $data = $methodGet->invoke($this->controller);
        $this->assertEquals($data, ['key' => 'value']);

        $data = $methodSet->invokeArgs($this->controller, [['is_array' => true]]);
        $this->assertEquals(is_array($data), true);

        $data = $methodGet->invoke($this->controller);
        $this->assertEquals($data, ['key' => 'value', 'is_array' => true]);
    }
}
