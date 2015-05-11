<?php

class ScribbitModel {
    protected $fileGlob = '*.{md,jpg,jpeg,png,gif}';
    
    public function getBitCount($path) {
        return count($this->getBits($path));
    }

    protected function getBits($path) {
        $files = glob($path, GLOB_BRACE);;
        rsort($files);
        return $files;
    }

    public function all() {
        $scribbits = array();
        $path = "../" . CONFIG::PROJECTS_PATH;
        foreach (glob($path . "*", GLOB_ONLYDIR) as $scribbit) {
            $t = filectime($scribbit);
            $scribbits[$t]['name'] = basename($scribbit);
            $scribbits[$t]['display_name'] = preg_replace('/_-_/', ' ', basename($scribbit));
            $scribbits[$t]['bit_count'] = $this->getBitCount($path . "/$scribbit/*");
        }

        krsort($scribbits);

        return $scribbits;
    }

    public function create($scribbit) {
        $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $scribbit);
        $name = preg_replace('/\W+/', '_-_', $ascii_name);

        mkdir("../" . CONFIG::PROJECTS_PATH . $name);
    }
    
    public function edit() {
        
    }

    public function delete($scribbit) {
        $path = "../" . CONFIG::PROJECTS_PATH . $scribbit;
        
        foreach ($this->getBits("$path/"  . $this->fileGlob) as $bit) {
            if (unlink($bit)) {
//                var_dump("success"); die;
            } else {
//                var_dump($bit); die;
            }
        }

        rmdir($path);
    }

}

class BitModel {
    
}
