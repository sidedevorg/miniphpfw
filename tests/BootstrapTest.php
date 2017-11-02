<?php

use PHPUnit\Framework\TestCase;

/**
 * BootstrapTest.
 */
class BootstrapTest extends TestCase
{
    /**
     * route caché path.
     *
     * @var string
     */
    private $route_cache_path = 'tests/helpers/files';

    /**
     * Config.
     *
     * @var array
     */
    private $config = [];

    /**
     * Set up.
     */
    public function setUp()
    {
        $this->removeCache();
        $this->config = [
            'lang' => 'en',
            'paths' => [
                'database' => 'tests/helpers/files/database.php',
                'middlewares' => 'tests/helpers/files/middlewares.php',
                'routes' => 'tests/helpers/files/routes.php',
                'env' => 'tests/helpers/files',
                'view' => 'tests/helpers/files/views',
                'i18n' => 'tests/helpers/files/langs',
                'routesCache' => $this->route_cache_path,
                'assets_manifest' => 'tests/helpers/files/mix-manifest.json',
            ],
            'config' => [
                'not_found_controller' => 'DummyController::not_found',
            ],
        ];
    }

    /**
     * Tear down.
     */
    public function tearDown()
    {
        $this->removeCache();
    }

    /**
     * Remove cache.
     */
    private function removeCache()
    {
        array_map('unlink', glob( $this->route_cache_path.'/*.cache'));
    }

    /**
     * Test load.
     */
    public function testLoad()
    {
        $this->removeCache();

        $response = (new SideDevOrg\MiniPhpFw\Bootstrap())->load($this->config);

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response->getResponse());
        $this->assertEquals(200, $response->getResponse()->getStatusCode());
    }

    /**
     * Test not found.
     */
    public function testNotFound()
    {
        $this->removeCache();

        $this->config['paths']['routes'] = 'tests/helpers/files/routes_404.php';

        $response = (new SideDevOrg\MiniPhpFw\Bootstrap())->load($this->config);

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response->getResponse());
        $this->assertEquals(404, $response->getResponse()->getStatusCode());
    }

    /**
     * Test Not Allowed.
     */
    public function testNotAllowed()
    {
        $this->removeCache();

        $this->config['paths']['routes'] = 'tests/helpers/files/routes_405.php';

        $response = (new SideDevOrg\MiniPhpFw\Bootstrap())->load($this->config);

        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $response->getResponse());
        $this->assertEquals(405, $response->getResponse()->getStatusCode());
    }
}
