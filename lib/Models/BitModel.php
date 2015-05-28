<?php

namespace Models;

use Config;
use GuzzleHttp\Client;
use Models\AbstractModel;
use Twig_Extension_StringLoader;
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
        $results = array();

        if (file_put_contents($this->absolutePath, $this->content) !== false) {
            $results['date']             = date(Config::DATE_FORMAT, filectime($this->absolutePath));
            $results['content']          = htmlspecialchars(file_get_contents($this->absolutePath));
            $results['name']             = $this->filename;
            $results['scribbit']         = $this->scribbit;

            // The bit may have some Twig syntax in it (like baseUrl() for image paths),
            // so have twig render the string the same as it would a variable in a Twig template
            $results['rendered_content'] = $this->app->view->render("string.twig", array("__str__" => $results['content']));

            return $results;
        }
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

        try {
            $client = new Client();
            $client->get($fromUrl, ['verify' => false, 'save_to' => $imgFile]);

            // Create a symlink in the web-accessible directory to the actual image file
            $source = APP_PATH . "public/img/" . basename($imgFile);

            $output = "";
            $cmd    = "ln -s $imgFile $source";
            $res    = exec($cmd, $output);

            // This is the markdown to go in the new bit file,
            // which contains a link to the new image file
            $content = "![image]({{baseUrl()}}/img/" . basename($imgFile) . ")";

            $this->setContent($content);
            return $this->saveContent();
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

            // Create a symlink in the web-accessible directory to the actual image file
            $source = APP_PATH . "public/img/" . basename($imgFile);

            $output = "";
            $cmd    = "ln -s $imgFile $source";
            $res    = exec($cmd, $output);

            // This is the markdown to go in the new bit file,
            // which contains a link to the new image file
            $content = "![image]({{baseUrl()}}/img/" . basename($imgFile) . ")";

            $this->setContent($content);
            return $this->saveContent();
        } catch (\Exception $e) {
            // Fail!
            $errors = $file->getErrors();

            return false;
        }
    }

}
