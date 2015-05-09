<?php
require '../vendor/autoload.php';
require '../session.php';
require '../config.php';
require '../controllers.php';
require '../services.php';

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

$pimple['ScribbitService'] = $pimple->share(function ($pimple) {
    return new ScribbitService($pimple);
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
    
    $pimple['app']->post('', function () use ($pimple) {    
        $pimple['ScribbitController']->create();
    });
    
    $pimple['app']->patch('/:name', function ($name) use ($pimple) {
        $pimple['ScribbitController']->update($name);
    });
    
    $pimple['app']->delete('/:name', function ($name) use ($pimple) {    
        $pimple['ScribbitController']->delete($name);
    });
});

$app->group('/bit', function () use ($pimple) {
    $pimple['app']->get('/:id', function ($id) use ($pimple) {
        $pimple['BitController']->find($id);
    });
    
    $pimple['app']->post('', function () use ($pimple) {    
        $pimple['BitController']->create();
    });
    
    $pimple['app']->patch('/:id', function ($id) use ($pimple) {
        $pimple['BitController']->update($id);
    });
    
    $pimple['app']->delete('/:id', function ($id) use ($pimple) {    
        $pimple['BitController']->delete($id);
    });
});

$app->post('/bit', function () use ($app, $session) {
    if(!$session->isAuthed()) {
        header('location: ./');
        exit();
    }
    
    $bit_name = time() . '-' . substr(md5(uniqid(rand(), true)),0, 8) . '.md';
    
    file_put_contents("../" . CONFIG::PROJECTS_PATH . $app->request->post('scribbit') . "/$bit_name", $app->request->post('bit'));
    
    header('location: /project/' . $app->request->post('scribbit'));
    exit();
});

// Run app
$app->run();


