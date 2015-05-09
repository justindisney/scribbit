<?php

class ScribbitModel {
    public function all() {
        $dirs = array();
        foreach (glob("../" . CONFIG::PROJECTS_PATH . "*", GLOB_ONLYDIR) as $dir) {
            $dirs[filectime($dir)] = basename($dir);
        }

        krsort($dirs);
        
        return $dirs;
    }

}

class BitModel {
    
}