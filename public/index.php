<?php
require '../vendor/autoload.php';
require '../session.php';
require '../config.php';
require '../controllers.php';
require '../models.php';

$session = new Session(CONFIG::APP_NAME, SCRIBBIT_PATH, CONFIG::USER, CONFIG::PASSWORD);

// Prepare app
$app = new \Slim\Slim(array(
    'templates.path' => '../templates',
));

// Create monolog logger and store logger in container as singleton
// (Singleton resources retrieve the same log resource definition each time)
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('slim-skeleton');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());

$pimple = new Pimple();
$pimple['app'] = $app;
$pimple['session'] = $session;

$pimple['AuthenticationController'] = $pimple->share(function ($pimple) {
    return new AuthenticationController($pimple);
});

$pimple['ScribbitController'] = $pimple->share(function ($pimple) {
    return new ScribbitController($pimple);
});

$pimple['ScribbitModel'] = $pimple->share(function ($pimple) {
    return new ScribbitModel($pimple);
});

$pimple['BitController'] = $pimple->share(function ($pimple) {
    return new BitController($pimple);
});

$pimple['BitModel'] = $pimple->share(function ($pimple) {
    return new BitModel($pimple);
});

// Define routes
$app->get('/', function () use ($pimple) {
    $pimple['ScribbitController']->all();
});

$app->post('/login', function () use ($pimple) {
    $pimple['AuthenticationController']->login();
});

$app->get('/logout', function () use ($pimple) {
    $pimple['AuthenticationController']->logout();
});

$app->group('/scribbit', function () use ($pimple) {
    $pimple['app']->get('/:name', function ($name) use ($pimple) {
        $pimple['ScribbitController']->find($name);
    });

    $pimple['app']->get('/download/:name', function ($name) use ($pimple) {
        $pimple['ScribbitController']->download($name);
    });

    $pimple['app']->post('', function () use ($pimple) {
        $pimple['ScribbitController']->post();
        $this->app->redirect('/');
    });

    $pimple['app']->put('/', function () use ($pimple) {
        $pimple['ScribbitController']->put();
        $this->app->redirect('/');
    });

    $pimple['app']->delete('/:name', function ($name) use ($pimple) {
        $pimple['ScribbitController']->delete($name);
        $this->app->redirect('/');
    });
});

$app->group('/bit', function () use ($pimple) {
    $pimple['app']->get('/:id', function ($id) use ($pimple) {
        $pimple['BitController']->find($id);
    });

    $pimple['app']->post('', function () use ($pimple) {
        $pimple['BitController']->create();
    });

    $pimple['app']->put('/:id', function ($id) use ($pimple) {
        $pimple['BitController']->update($id);
    });

    $pimple['app']->delete('/:id', function ($id) use ($pimple) {
        $pimple['BitController']->delete($id);
    });
});

// Run app
$app->run();