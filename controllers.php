<?php

abstract class Controller
{
    protected $app;
    protected $model;
    protected $session;

    public function __construct(Pimple $di)
    {
        $this->app     = $di['app'];
        $this->session = $di['session'];
        $this->init($di);
    }

    public abstract function init(Pimple $di);
}

class AuthenticationController extends Controller
{
    public function init(Pimple $di)
    {

    }

    public function login()
    {
        $username = $this->app->request->post('username');
        $password = $this->app->request->post('password');

        if ($this->session->login($username, $password)) {
            $this->app->redirect('/');
        } else {
            // TODO: handle failed login
        }
    }

    public function logout()
    {
        $this->session->logout();
        $this->app->redirect('/');
    }

}

class ScribbitController extends Controller
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
            foreach (glob("../" . CONFIG::PROJECTS_PATH . "$dir_name/*") as $file) {
                $d                     = date("F j Y H:i:s", filectime($file));
                $files[$d]['contents'] = file_get_contents($file);
                $files[$d]['name']     = basename($file);
            }

            krsort($files);

            $this->app->render('project.html', array(
                'scribbit' => $name,
                'bits'     => $files
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
            $this->app->render('projects.html', array(
                'scribbits' => $this->model->all()
            ));
        } else {
            $this->app->render('login.html', array(
                'hideLogout' => true
            ));
        }
    }

}

class BitController extends Controller
{
    public function init(Pimple $di)
    {
        $this->model = $di['BitModel'];
    }

    public function create()
    {
        if ($this->session->isAuthed()) {
            $bit_name = time() . '-' . substr(md5(uniqid(rand(), true)), 0, 8) . '.md';
            $path     = "../" . CONFIG::PROJECTS_PATH . $this->app->request->post('scribbit') . "/$bit_name";

            file_put_contents($path, $this->app->request->post('bit'));

            $this->app->redirect('/scribbit/' . $this->app->request->post('scribbit'));
        }
    }

}
