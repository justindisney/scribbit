<?php

namespace Controllers;

use Controllers\AbstractController;
use Pimple;
use Models\BitModel;

class BitController extends AbstractController
{
    public function init(Pimple $di)
    {

    }

    public function post()
    {
        if ($this->session->isAuthed()) {
            $bit = new BitModel(array(
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
            $bit = new BitModel(array(
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
            $bit = new BitModel(array(
                'scribbit' => $scribbit,
                'filename' => $filename
            ));

            $zipFile = $bit->download();

            if ($zipFile) {
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
            $bit = new BitModel(array(
                'scribbit' => $scribbit,
                'filename' => $filename
            ));

            $bit->delete();
        }
    }

}
