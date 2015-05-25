<?php

namespace Models;

use Config;
use GuzzleHttp\Client;
use Models\AbstractModel;
use ZipArchive;

class BitModel extends AbstractModel
{
    protected $absolutePath;
    protected $content;
    protected $filename;
    protected $scribbit;
    protected $scribbitPath;

    public function init($params = array())
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

        $this->scribbitPath = APP_PATH . Config::SCRIBBITS_DIRECTORY . $this->scribbit . "/";
        $this->absolutePath = $this->scribbitPath . $this->filename;
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
        $zipFile = $this->scribbitPath . $this->filename . ".zip";

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE)) {
            $zip->addFile($this->absolutePath, $this->filename);
            $zip->close();
        } else {
            return false;
        }

        if (file_exists($zipFile)) {
            return $zipFile;
        } else {
            return false;
        }
    }

    public function delete()
    {
        $info     = pathinfo($this->absolutePath);
        $dirName  = $info['dirname'];
        $fileName = $info['filename'];

        // remove any symlinks to image files
        foreach (glob(APP_PATH . "public/img/$fileName.*") as $file) {
            unlink($file);
        }

        // now remove the actual bit files
        foreach (glob("$dirName/$fileName.*") as $file) {
            unlink($file);
        }
    }

    public function saveWebImage($fromUrl)
    {
        $urlInfo  = pathinfo($fromUrl);
        $fileInfo = pathinfo($this->filename);

        // Use the same filename as the bit filename, 
        // but use the extension from the image being downloaded (after cleaning it up)
        preg_match("/^([a-zA-Z0-9]*)/", $urlInfo['extension'], $matches);
        $imgFile = $this->scribbitPath . $fileInfo['filename'] . "." . $matches[0];

        // This is the markdown to go in the new bit file,
        // which contains a link to the new image file
        $content = "![image]({{baseUrl()}}/img/" . basename($imgFile) . ")";

        try {
            $client = new Client();
            $client->get($fromUrl, ['verify' => false, 'save_to' => $imgFile]);

            $this->setContent($content);
            $this->saveContent();

            $source = APP_PATH . "public/img/" . basename($imgFile);

            $output = "";
            $cmd    = "ln -s $imgFile $source";
            $res    = exec($cmd, $output);
        } catch (Exception $e) {
            // Log the error or something
            return false;
        }
    }

    public function saveUploadedImage()
    {
        $fileInfo = pathinfo($this->filename);
        $storage  = new \Upload\Storage\FileSystem($this->scribbitPath);
        $file     = new \Upload\File('uploadedImage', $storage);
        $ext      = $file->getExtension();

        // Use the same filename as the bit filename, 
        // but use the extension from the image being downloaded (after cleaning it up)
        preg_match("/^([a-zA-Z0-9]*)/", $ext, $matches);
        $imgFile = $this->scribbitPath . $fileInfo['filename'] . "." . $matches[0];
        $file->setName($fileInfo['filename']);

        try {
            // Success!
            $file->upload();
            
            // This is the markdown to go in the new bit file,
            // which contains a link to the new image file
            $content = "![image]({{baseUrl()}}/img/" . basename($imgFile) . ")";
            
            $this->setContent($content);
            $this->saveContent();
            
            $source = APP_PATH . "public/img/" . basename($imgFile);

            $output = "";
            $cmd    = "ln -s $imgFile $source";
            $res    = exec($cmd, $output);
        } catch (\Exception $e) {
            // Fail!
            $errors = $file->getErrors();
        }
    }

}
