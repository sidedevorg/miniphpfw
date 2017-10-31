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
}
