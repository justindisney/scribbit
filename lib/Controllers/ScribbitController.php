<?php

namespace Controllers;

use CONFIG;
use Pimple;
use Controllers\AbstractController;

class ScribbitController extends AbstractController
{
    public function init(Pimple $di)
    {
        $this->model = $di['ScribbitModel'];
    }

    public function find($name)
    {
        if ($this->session->isAuthed()) {
            $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $name);
            $dir_name   = preg_replace('/\W+/', '-', $ascii_name);

            $files = array();
            foreach (glob("../" . CONFIG::SCRIBBITS_DIRECTORY . "$dir_name/*") as $file) {
                $d                     = date(CONFIG::DATE_FORMAT, filectime($file));
                $files[$d]['contents'] = file_get_contents($file);
                $files[$d]['name']     = basename($file);
            }

            krsort($files);

            $this->app->render('scribbit.twig', array(
                'scribbit' => $name,
                'scribbit_display' => preg_replace('/_-_/', ' ', $name),
                'bits' => $files
            ));
        } else {
            $this->app->redirect('/');
        }
    }

    public function download($name)
    {
        if ($this->session->isAuthed()) {
            $this->model->download($name);
        }
    }

    public function post()
    {
        if ($this->session->isAuthed()) {
            $this->model->create($this->app->request->post('scribbit'));
        }
    }

    public function put()
    {
        if ($this->session->isAuthed()) {
            $old = $this->app->request->put('pk');
            $new = $this->app->request->put('value');

            $this->model->update($old, $new);
        }
    }

    public function delete($name)
    {
        if ($this->session->isAuthed()) {
            $this->model->delete($name);
        }
    }

    public function all()
    {
        if ($this->session->isAuthed()) {
            $this->app->render('scribbits.twig', array(
                'scribbits' => $this->model->all()
            ));
        } else {
            $this->app->render('login.twig', array(
                'hideLogout' => true
            ));
        }
    }

}
