<?php

class ScribbitService {
    protected $app;
    protected $session;

    public function __construct(Pimple $di) {
        $this->app = $di['app'];
        $this->session = $di['session'];
    }

    public function all() {
        $dirs = array();
        foreach (glob("../" . CONFIG::PROJECTS_PATH . "*", GLOB_ONLYDIR) as $dir) {
            $dirs[filectime($dir)] = basename($dir);
        }

        krsort($dirs);
        
        return $dirs;
    }

}
