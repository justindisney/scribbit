<?php

namespace Models;

use Config;
use ZipArchive;

class BitModel
{
    protected $absolutePath;
    protected $content;
    protected $filename;
    protected $scribbit;

    public function __construct($params = array())
    {
        if (isset($params['filename'])) {
            $this->filename = $params['filename'];
        } else {
            $this->filename = time() . '-' . substr(md5(uniqid(rand(), true)), 0, 8) . '.md';
        }

        if (isset($params['scribbit'])) {
            $this->scribbit = $params['scribbit'];
        } else {
            $this->scribbit = Config::LOST_AND_FOUND;
        }

        $foo = APP_PATH;
        $this->absolutePath = APP_PATH . Config::SCRIBBITS_DIRECTORY . $this->scribbit . "/" . $this->filename;
    }

    public function getFileName()
    {
        return $this->filename;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function saveContent()
    {
        file_put_contents($this->absolutePath, $this->content);
    }

    public function download()
    {
        $zipPath = sys_get_temp_dir() . "/" . $this->filename . ".zip";

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE)) {
            $zip->addFile($this->absolutePath, $this->filename);
            $zip->close();
            return $zipPath;
        }

        return false;
    }

    public function delete()
    {
        unlink($this->absolutePath);
    }

}
