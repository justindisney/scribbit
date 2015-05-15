<?php

namespace Controllers;

use Pimple;
use Controllers\AbstractController;

class AuthenticationController extends AbstractController
{
    public function init(Pimple $di)
    {

    }

    public function login()
    {
        $username = $this->app->request->post('username');
        $password = $this->app->request->post('password');

        if ($this->session->login($username, $password)) {
            $this->app->redirect($this->app->urlFor('home'));
        } else {
            // TODO: handle failed login
        }
    }

    public function logout()
    {
        $this->session->logout();
        $this->app->redirect($this->app->urlFor('home'));
    }

}
