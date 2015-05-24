<?php

namespace Models;

use Config;
use ZipArchive;
use Models\AbstractModel;

class ScribbitModel extends AbstractModel
{
    protected $fileGlob = '*.{md,jpg,jpeg,png,gif}';

    public function init($params = array())
    {
        
    }

    public function getBitCount($path)
    {
        return count($this->getBits($path));
    }

    protected function getBits($path)
    {
        $files = glob($path, GLOB_BRACE);
        rsort($files);
        return $files;
    }

    public function all()
    {
        $scribbits = array();
        $path      = APP_PATH . Config::SCRIBBITS_DIRECTORY;
        foreach (glob($path . "*", GLOB_ONLYDIR) as $scribbit) {
            if (basename($scribbit) != Config::LOST_AND_FOUND) {
                $t                             = filectime($scribbit);
                $scribbits[$t]['name']         = basename($scribbit);
                $scribbits[$t]['display_name'] = preg_replace('/_-_/', ' ', basename($scribbit));
                $scribbits[$t]['bit_count']    = $this->getBitCount("$scribbit/*.{md}");
            }
        }

        krsort($scribbits);

        return $scribbits;
    }

    public function download($name)
    {
        $path    = APP_PATH . Config::SCRIBBITS_DIRECTORY . $name;
        $zipFile = $path . "/$name.zip";

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE)) {
            foreach ($this->getBits("$path/" . $this->fileGlob) as $bit) {
                $zip->addFile($bit, basename($bit));
            }
            $zip->close();

            if (file_exists($zipFile)) {
                header("Content-type: application/zip");
                header("Content-Disposition: attachment; filename=$name.zip");
                header("Content-length: " . filesize($zipFile));
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile($zipFile);
                unlink($zipFile);
            }
        }
    }

    public function create($scribbit)
    {
        $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $scribbit);
        $name       = preg_replace('/\W+/', '_-_', $ascii_name);

        mkdir(APP_PATH . Config::SCRIBBITS_DIRECTORY . $name);
    }

    public function update($old, $new)
    {
        $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $new);
        $name       = preg_replace('/\W+/', '_-_', $ascii_name);

        if (rename(APP_PATH . Config::SCRIBBITS_DIRECTORY . $old, APP_PATH . Config::SCRIBBITS_DIRECTORY . $name)) {
            return $name;
        } else {
            return false;
        }
    }

    public function delete($scribbit)
    {
        $path = APP_PATH . Config::SCRIBBITS_DIRECTORY . $scribbit;

        foreach ($this->getBits("$path/" . $this->fileGlob) as $bit) {
            if (unlink($bit)) {
//                var_dump("success"); die;
            } else {
//                var_dump($bit); die;
            }
        }

        rmdir($path);
    }

}
