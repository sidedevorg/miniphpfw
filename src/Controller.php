<?php

namespace SideDevOrg\MiniPhpFw;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;

/**
 * Framework Controller.
 */
class Controller
{
    /**
     * Data array container.
     *
     * @var array
     */
    private $data = [];

    /**
     * I18n array container.
     *
     * @var array
     */
    private $i18n = [];

    /**
     * Request.
     *
     * @var \Psr\Http\Message\RequestInterface
     */
    private $request;

    /**
     * Mustache instance.
     *
     * @var Mustache_Engine
     */
    private $mustacheInstance;

    /**
     * Set request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function setRequest(\Psr\Http\Message\RequestInterface $request) : \Psr\Http\Message\RequestInterface
    {
        return $this->request = $request;
    }

    /**
     * Get header.
     *
     * @param string $name
     *
     * @return mixed srtring or null
     */
    protected function header(string $name)
    {
        return isset($this->request->getHeaders()[$name]) ?
            $this->request->getHeaders()[$name][0] : null;
    }

    /**
     * Get input.
     *
     * @param string $name
     * @param mixed  $defaultValue
     * @param bool   $autoTrim
     *
     * @return mixed
     */
    protected function input(string $name, $defaultValue = null, bool $autoTrim = true)
    {
        $value = false;

        if ($this->request->getMethod() === 'GET') {
            if (isset($this->request->getQueryParams()[$name])) {
                $value = $this->request->getQueryParams()[$name];
            }
        }

        if ($this->request->getMethod() === 'POST') {
            if (isset($this->request->getParsedBody()[$name])) {
                $value = $this->request->getParsedBody()[$name];
            }
        }

        if ($value) {
            return (!$autoTrim) ? $value : trim($value);
        }

        return $defaultValue;
    }

    /**
     * Set data.
     *
     * @param mixed $key   string or array
     * @param mixed $value if $key is string
     *
     * @return mixed
     */
    protected function data($key, $value = false)
    {
        if (is_array($key)) {
            return $this->data = array_merge($this->data, $key);
        }

        return $this->data[$key] = $value;
    }

    /**
     * Get data.
     *
     * @return array
     */
    protected function getData() : array
    {
        return $this->data;
    }

    /**
     * Get lang key in file.key format.
     *
     * @param string      $fileKey
     * @param bool|string $changeLang false or lang code
     *
     * @return string
     */
    protected function lang($fileKey, $changeLang = false) : string
    {
        $lang = (!$changeLang) ? $this->header('lang') : $changeLang;

        $search = explode('.', $fileKey);
        $file = $search[0].'.php';
        $key = isset($search[1]) ? $search[1] : '';

        $route = $this->config('paths.i18n').'/'.$lang.'/'.$file;
        if (!file_exists($route)) {
            return $fileKey;
        }

        $data = require_once $route;

        if (is_array($data)) {
            $this->i18n[$lang][$file] = $data;
        }

        return isset($this->i18n[$lang][$file][$key]) ?
            $this->i18n[$lang][$file][$key] :
            $fileKey
        ;
    }

    /**
     * Get config item.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function config(string $key)
    {
        $config = json_decode($this->header('config'), true);
        $definition = explode('.', $key);
        $item = isset($config[$definition[0]]) ? $config[$definition[0]] : null;

        $numberOfDefinitions =  count($definition);

        for ($i = 1; $i < $numberOfDefinitions; ++$i) {
            $item = isset($item[$definition[$i]]) ? $item[$definition[$i]] : null;
        }

        return $item;
    }

    /**
     * Get view.
     *
     * @param string $template
     *
     * @return string
     */
    protected function view(string $template, array $data = []) : string
    {
        if (!$this->mustacheInstance) {
            $viewsRoute = $this->config('paths.view');
            $options = ['extension' => '.hbs'];

            $this->mustacheInstance = new Mustache_Engine(array(
                'loader' => new Mustache_Loader_FilesystemLoader($viewsRoute, $options),
                'partials_loader' => new Mustache_Loader_FilesystemLoader($viewsRoute, $options),
                'charset' => 'UTF-8',
            ));
        }

        $tpl = $this->mustacheInstance->loadTemplate($template);
        $data = array_merge($this->getData(), $data);

        return $tpl->render($this->mapData($data));
    }

    /**
     * Map data to output.
     *
     * @param array $data
     *
     * @return array
     */
    private function mapData(array $data) : array
    {
        $assets = [
            'js' => false,
            'css' => false,
        ];

        $assets_path = $this->config('paths.assets_manifest');

        if (file_exists($assets_path)) {
            $assets_data = json_decode(file_get_contents($assets_path), true);
            $assets = [
                'js' => $assets_data['/app.js'],
                'css' => $assets_data['/app.css'],
            ];
        }

        $tplData = [
            'fw' => [
                'assets' => $assets,
                'i18n' => [],
                'data' => $data,
            ],
        ];

        return $tplData;
    }

    /**
     * Get "not found" view.
     *
     * @return string
     */
    public function not_found() : string
    {
        return $this->view('404');
    }
}
