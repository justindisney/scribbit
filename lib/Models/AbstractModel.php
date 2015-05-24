<?php

namespace Models;

use Pimple;

abstract class AbstractModel
{
    protected $app;
    protected $di;
    protected $session;

    public function __construct(Pimple $di)
    {
        $this->app     = $di['app'];
        $this->session = $di['session'];
        $this->di      = $di;
    }

    public abstract function init($params = array());
}
