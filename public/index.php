<?php
require '../vendor/autoload.php';
require '../session.php';
require '../config.php';

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

// Define routes
$app->get('/', function () use ($app, $session) {
    if($session->isAuthed()) {
        $app->render('projects.html');
    } else {
        $app->render('login.html');
    }
});

$app->post('/login', function () use ($app, $session) {
    $username = $app->request->post('username');
    $password = $app->request->post('password');
    
    if ($session->login($username, $password)) {
        header('location: ./');
        exit();
    }
});

// Run app
$app->run();
