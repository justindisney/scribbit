<?php

namespace Models;

use Config;
use Pimple;
use ZipArchive;
use Models\AbstractModel;

class ScribbitModel extends AbstractModel
{
    protected $fileGlob = '*.{md,jpg,jpeg,png,gif}';
    protected $absoluteScribbitsPath;
    protected $name;

    public function __construct(Pimple $di)
    {
        parent::__construct($di);

        $this->absoluteScribbitsPath = APP_PATH . Config::SCRIBBITS_DIRECTORY;
    }

    private function sanitizeName($name)
    {
        $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $name);
        return preg_replace('/\W+/', '_-_', $ascii_name);
    }

    public function init($params = array())
    {
        if (isset($params['name'])) {
            $this->name = $this->sanitizeName($params['name']);
        } else {
            $this->name = Config::LOST_AND_FOUND;
        }
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

        foreach (glob($this->absoluteScribbitsPath . "*", GLOB_ONLYDIR) as $scribbit) {
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
        $path    = $this->absoluteScribbitsPath . $name;
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
        $newDirectory = $this->absoluteScribbitsPath . $this->sanitizeName($scribbit);
        
        if (!file_exists($newDirectory)) {
            mkdir($newDirectory);
        }
    }

    public function update($old, $new)
    {
        $cleanOld = $this->sanitizeName($old);
        $cleanNew = $this->sanitizeName($new);

        if (file_exists($this->absoluteScribbitsPath . $cleanNew)) {
            $this->app->halt(500, json_encode(array('status' => "Scribbit $cleanNew exists")));
        }
        
        if (rename($this->absoluteScribbitsPath . $cleanOld, $this->absoluteScribbitsPath . $cleanNew)) {
            return array ('old' => $cleanOld, 'new' => $cleanNew, 'display' => $new);
        } else {
            $this->app->halt(500, json_encode(array('status' => "Scribbit renaming failed")));
        }
    }

    public function delete($scribbit)
    {
        $path = $this->absoluteScribbitsPath . $scribbit;

        foreach (glob("$path/*.md") as $file) {
            $bit = new BitModel($this->di);

            $bit->init(array(
                'scribbit' => $scribbit,
                'filename' => basename($file)
            ));

            $bit->delete();
        }

        rmdir($path);
    }

}
