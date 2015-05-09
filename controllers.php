<?php

abstract class Controller {

    protected $app;
    protected $service;

    public function __construct(Pimple $di) {
        $this->app = $di['app'];
        $this->init($di);
    }

    public abstract function init(Pimple $di);
}

class AuthenticationController extends Controller {
    protected $session;
    
    public function init(Pimple $di) {
        $this->app = $di['app'];
        $this->session = $di['session'];
    }

    public function login() {
        $username = $this->app->request->post('username');
        $password = $this->app->request->post('password');

        if ($this->session->login($username, $password)) {
            $this->app->redirect('/');
        } else {
            // TODO: handle failed login
        }
    }
    
    public function logout() {
        $this->session->logout();
        $this->app->redirect('/');
    }

}

class ScribbitController extends Controller {
    protected $session;
    
    public function init(Pimple $di) {
        $this->app = $di['app'];
        $this->session = $di['session'];
        $this->service = $di['ScribbitService'];
    }
    
    public function find($name) {
        if($this->session->isAuthed()) {
            $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $name);
            $dir_name = preg_replace('/\W+/', '-', $ascii_name);

            $files = array();
            foreach (glob("../" . CONFIG::PROJECTS_PATH . "$dir_name/*") as $file) {
                $d = date("F j Y H:i:s", filectime($file));
                $files[$d]['contents'] = file_get_contents($file);
                $files[$d]['name'] = basename($file);
            }

            krsort($files);

            $this->app->render('project.html', array(
                'scribbit' => $name,
                'bits' => $files
            ));
        } else {
            $this->app->redirect('/');
        }
    }

    public function create() {
        if($this->session->isAuthed()) {
            $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $this->app->request->post('name'));
            $name = preg_replace('/\W+/', '-', $ascii_name);

            mkdir("../" . CONFIG::PROJECTS_PATH . $name);
        }
        
        $this->app->redirect('/');
    }
    
    public function all() {
        if($this->session->isAuthed()) {
            $this->app->render('projects.html', array(
                'dirs' => $this->service->all()
            ));
        } else {
            $this->app->render('login.html');
        }
    }

}

class BitController extends Controller {
    protected $session;
    
    public function init(Pimple $di) {
        $this->app = $di['app'];
        $this->session = $di['session'];
        $this->service = $di['ScribbitService'];
    }

    public function create() {
        if($this->session->isAuthed()) {
            $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $this->app->request->post('name'));
            $name = preg_replace('/\W+/', '-', $ascii_name);

            mkdir("../" . CONFIG::PROJECTS_PATH . $name);
        }
        
        $this->app->redirect('/');
        
        if($this->session->isAuthed()) {
            $bit_name = time() . '-' . substr(md5(uniqid(rand(), true)),0, 8) . '.md';
            $path = "../" . CONFIG::PROJECTS_PATH . $this->app->request->post('scribbit') . "/$bit_name";
            
            file_put_contents($path, $this->app->request->post('bit'));

            $this->app->redirect('/scribbit/' . $this->app->request->post('scribbit'));
        } else {
            $this->app->redirect('/');
        }
    }

}

