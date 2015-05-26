<?php
require '../vendor/autoload.php';

use \Config;
use \Session;
use \Controllers;
use \Models;

$session = new Session(Config::APP_NAME, APP_PATH, Config::USER, Config::PASSWORD);

// Prepare app
$app = new \Slim\Slim(array(
    'templates.path' => APP_PATH . 'templates',
));

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => false, //realpath(APP_PATH . 'templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

$pimple = new Pimple();
$pimple['app'] = $app;
$pimple['session'] = $session;

$pimple['AuthenticationController'] = $pimple->share(function ($pimple) {
    return new \Controllers\AuthenticationController($pimple);
});

$pimple['ScribbitController'] = $pimple->share(function ($pimple) {
    return new \Controllers\ScribbitController($pimple);
});

$pimple['ScribbitModel'] = $pimple->share(function ($pimple) {
    return new \Models\ScribbitModel($pimple);
});

$pimple['BitController'] = $pimple->share(function ($pimple) {
    return new \Controllers\BitController($pimple);
});

$pimple['BitModel'] = $pimple->share(function ($pimple) {
    return new \Models\BitModel($pimple);
});

// Define routes
$app->get('/', function () use ($pimple) {
    $pimple['ScribbitController']->all();
})->name('home');

$app->post('/login', function () use ($pimple) {
    $pimple['AuthenticationController']->login();
})->name('login');

$app->get('/logout', function () use ($pimple) {
    $pimple['AuthenticationController']->logout();
})->name('logout');

$app->group('/scribbit', function () use ($pimple) {
    $pimple['app']->get('/:scribbit', function ($scribbit) use ($pimple) {
        $pimple['ScribbitController']->find($scribbit);
    })->name('scribbit-get');

    $pimple['app']->get('/download/:scribbit', function ($scribbit) use ($pimple) {
        $pimple['ScribbitController']->download($scribbit);
    })->name('scribbit-download');

    $pimple['app']->post('', function () use ($pimple) {
        $pimple['ScribbitController']->post();
        $pimple['app']->redirect($pimple['app']->urlFor('home')); // refresh the page
    });

    $pimple['app']->put('/', function () use ($pimple) {
        $pimple['ScribbitController']->put();
    });

    $pimple['app']->delete('/:scribbit', function ($scribbit) use ($pimple) {
        $pimple['ScribbitController']->delete($scribbit);
    })->name('scribbit-delete');
});

$app->group('/bit', function () use ($pimple) {
    $pimple['app']->get('/download/:scribbit/:bit', function ($scribbit, $bit) use ($pimple) {
        $pimple['BitController']->download($scribbit, $bit);
    })->name('bit-download');

    $pimple['app']->post('', function () use ($pimple) {
        $pimple['BitController']->post();
    })->name('bit-post');

    $pimple['app']->put('', function () use ($pimple) {
        $pimple['BitController']->put();
    })->name('bit-put');

    $pimple['app']->delete('/:scribbit/:bit', function ($scribbit, $bit) use ($pimple) {
        $pimple['BitController']->delete($scribbit, $bit);
    })->name('bit-delete');
});

// Run app
$app->run();