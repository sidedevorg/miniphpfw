<?php

/**
 * Dummy controller.
 */
class DummyController extends SideDevOrg\MiniPhpFw\Controller
{
    public function dummyMethod()
    {
        return 'dummy';
    }

    public function not_found() : string
    {
        return parent::not_found();
    }
}
