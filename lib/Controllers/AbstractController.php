<?php

namespace Controllers;

use Pimple;

abstract class AbstractController
{
    protected $app;
    protected $model;
    protected $session;

    public function __construct(Pimple $di)
    {
        $this->app     = $di['app'];
        $this->session = $di['session'];

        $this->init($di);
    }

    public abstract function init(Pimple $di);
}
