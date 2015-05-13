<?php

class ScribbitModel
{
    protected $fileGlob = '*.{md,jpg,jpeg,png,gif}';

    public function getBitCount($path)
    {
        return count($this->getBits($path));
    }

    protected function getBits($path)
    {
        $files = glob($path, GLOB_BRACE);
        ;
        rsort($files);
        return $files;
    }

    public function all()
    {
        $scribbits = array();
        $path      = "../" . CONFIG::SCRIBBITS_DIRECTORY;
        foreach (glob($path . "*", GLOB_ONLYDIR) as $scribbit) {
            if (basename($scribbit) != CONFIG::LOST_AND_FOUND) {
                $t                             = filectime($scribbit);
                $scribbits[$t]['name']         = basename($scribbit);
                $scribbits[$t]['display_name'] = preg_replace('/_-_/', ' ', basename($scribbit));
                $scribbits[$t]['bit_count']    = $this->getBitCount($path . "/$scribbit/*");
            }
        }

        krsort($scribbits);

        return $scribbits;
    }

    public function download($name)
    {
        $path    = "../" . CONFIG::SCRIBBITS_DIRECTORY . $name;
        $zipPath = sys_get_temp_dir() . "/$name.zip";

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE)) {
            foreach ($this->getBits("$path/" . $this->fileGlob) as $bit) {
                $zip->addFile($bit, basename($bit));
            }
            $zip->close();

            header("Content-type: application/zip");
            header("Content-Disposition: attachment; filename=$name.zip");
            header("Content-length: " . filesize($zipPath));
            header("Pragma: no-cache");
            header("Expires: 0");
            readfile($zipPath);
            unlink($zipPath);
        }
    }

    public function create($scribbit)
    {
        $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $scribbit);
        $name       = preg_replace('/\W+/', '_-_', $ascii_name);

        mkdir("../" . CONFIG::SCRIBBITS_DIRECTORY . $name);
    }

    public function update($old, $new)
    {
        $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $new);
        $name       = preg_replace('/\W+/', '_-_', $ascii_name);

        rename("../" . CONFIG::SCRIBBITS_DIRECTORY . $old, "../" . CONFIG::SCRIBBITS_DIRECTORY . $name);
    }

    public function delete($scribbit)
    {
        $path = "../" . CONFIG::SCRIBBITS_DIRECTORY . $scribbit;

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
            $this->scribbit = CONFIG::LOST_AND_FOUND;
        }

        $this->absolutePath = APP_PATH . CONFIG::SCRIBBITS_DIRECTORY . $this->scribbit . "/" . $this->filename;
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
