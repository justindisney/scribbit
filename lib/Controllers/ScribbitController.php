<?php

namespace Controllers;

use Config;
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
            $i = 1; // add this to the end of the filectime value, in case files have the same filectime
            foreach (glob(APP_PATH . Config::SCRIBBITS_DIRECTORY . "$dir_name/*.{md}", GLOB_BRACE) as $file) {
                $d                     = date(Config::DATE_FORMAT, filectime($file));
                $files[$d . "$i"]['contents'] = htmlspecialchars(file_get_contents($file));
                $files[$d . "$i"]['name']     = basename($file);
                $i++;
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

            $res = $this->model->update($old, $new);

            if ($res !== false) {
                $response = array ('old' => $old, 'new' => $res, 'display' => $new);
                echo json_encode($response);
            }
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
