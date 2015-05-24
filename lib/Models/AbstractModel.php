<?php

namespace Models;

use Pimple;

abstract class AbstractModel
{
    protected $app;
    protected $model;
    protected $session;

    public function __construct(Pimple $di)
    {
        $this->app     = $di['app'];
        $this->session = $di['session'];
    }

    public abstract function init($params = array());
}
