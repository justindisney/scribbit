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
        $dirs = array();
        foreach (glob("../" . CONFIG::PROJECTS_PATH . "*", GLOB_ONLYDIR) as $dir) {
            $dirs[filectime($dir)] = basename($dir);
        }
        
        krsort($dirs);
        
        $app->render('projects.html', array(
            'dirs' => $dirs
        ));
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

$app->get('/logout', function () use ($app, $session) {
    $session->logout();
    header('location: ./');
    exit();
});

$app->post('/project', function () use ($app, $session) {
    $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $app->request->post('name'));
    $name = preg_replace('/\W+/', '-', $ascii_name);
    
    mkdir("../" . CONFIG::PROJECTS_PATH . $name);
    
    header('location: ./');
    exit();
});

$app->get('/project/:name', function ($name) use ($app, $session) {
    if($session->isAuthed()) {
        $ascii_name = iconv('UTF-8', 'ASCII//IGNORE', $name);
        $dir_name = preg_replace('/\W+/', '-', $ascii_name);
        
        $files = array();
        foreach (glob("../" . CONFIG::PROJECTS_PATH . "$dir_name/*") as $file) {
            $files[date("F j Y H:i:s", filectime($file))] = file_get_contents($file);
        }
        
        krsort($files);
        
        $app->render('project.html', array(
            'scribbit' => $name,
            'bits' => $files
        ));
    } else {
        header('location: ./');
        exit();
    }
});

// Run app
$app->run();


