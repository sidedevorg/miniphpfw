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
    private $data;

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
     * @return mixed
     */
    public function header(string $name) : string
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
    public function input(string $name, mixed $defaultValue = null, bool $autoTrim = true)
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
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function data(string $key, $value)
    {
        return $this->data[$key] = $value;
    }

    /**
     * Get view.
     *
     * @param string $template
     *
     * @return string
     */
    public function view(string $template, array $data = []) : string
    {
        if (!$this->mustacheInstance) {
            $viewsRoute = 'app/views';
            $options = ['extension' => '.hbs'];

            $this->mustacheInstance = new Mustache_Engine(array(
              'loader' => new Mustache_Loader_FilesystemLoader($viewsRoute, $options),
              'partials_loader' => new Mustache_Loader_FilesystemLoader($viewsRoute, $options),
              'charset' => 'UTF-8',
            ));
        }

        $tpl = $this->mustacheInstance->loadTemplate($template);

        $this->data = array_merge($data, $this->data);

        return $tpl->render($this->data);
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
