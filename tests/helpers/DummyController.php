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
        return 'not found';
    }
}
