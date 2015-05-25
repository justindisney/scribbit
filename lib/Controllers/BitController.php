<?php

namespace Controllers;

use Controllers\AbstractController;
use Pimple;
use Models\BitModel;

class BitController extends AbstractController
{

    protected $di;

    public function init(Pimple $di)
    {
        $this->di = $di;
    }

    public function post()
    {
        if ($this->session->isAuthed()) {
            $bit = new BitModel($this->di);
            $bit->init(array(
                'scribbit' => $this->app->request->post('scribbit')
            ));
            $bit->setContent($this->app->request->post('content'));
            $bit->saveContent();

            return $bit->getFileName();
        }
    }

    public function put()
    {
        if ($this->session->isAuthed()) {
            $bit = new BitModel($this->di);
            $bit->init(array(
                'scribbit' => $this->app->request->put('scribbit'),
                'filename' => $this->app->request->put('bit')
            ));

            $bit->setContent($this->app->request->put('content'));
            $bit->saveContent();

            return $bit->getFileName();
        }
    }

    public function download($scribbit, $filename)
    {
        if ($this->session->isAuthed()) {
            $bit = new BitModel($this->di);
            $bit->init(array(
                'scribbit' => $scribbit,
                'filename' => $filename
            ));

            $zipFile = $bit->download();

            if (file_exists($zipFile)) {
                header("Content-type: application/zip");
                header("Content-Disposition: attachment; filename=$scribbit-$filename.zip");
                header("Content-length: " . filesize($zipFile));
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile($zipFile);
                unlink($zipFile);
            }
        }
    }

    public function delete($scribbit, $filename)
    {
        if ($this->session->isAuthed()) {
            $bit = new BitModel($this->di);
            $bit->init(array(
                'scribbit' => $scribbit,
                'filename' => $filename
            ));

            $bit->delete();
        }
    }

    public function saveWebImage($scribbit)
    {
        if ($this->session->isAuthed()) {
            $bit = new BitModel($this->di);
            $bit->init(array(
                'scribbit' => $scribbit
            ));

            $bit->saveWebImage($this->app->request->post('image_url'));
        }
    }

    public function saveUploadedImage($scribbit)
    {
        if ($this->session->isAuthed()) {
            $bit = new BitModel($this->di);
            $bit->init(array(
                'scribbit' => $scribbit
            ));
            
            $bit->saveUploadedImage();
        }
    }

}
