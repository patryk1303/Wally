<?php

define('URL', 'http://localhost/Wally/');

require_once 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
require_once 'Smarty/Smarty.class.php';
require_once 'Smarty.php';

require_once 'Controllers/RegisterControllers.php';


$config = array(
    'view' => new \Slim\Views\Smarty(),
    'debug' => true,
    'templates.path' => './templates'
);


$app = new \Slim\Slim($config);

$view = $app->view();
$view->parserDirectory = dirname(__FILE__) . '/templates';

$plug = dirname(__FILE__) . '/SmartyPlugins';
$view->parserExtensions = array($plug);

$app->setName('Wally');

$app->get('/', function() use ($app) {
    $app->render('main.html');
});

$app->get('/contact', function() use ($app) {
    $app->render('contact.html');
});
$app->post('/contact', function() use ($app) {
    $postData = $app->request->post();
    $correct = checkcontact($postData);
    
    $arr = array(
        'correct'   =>  $correct
    );
    $app->render('contact_post.html', $arr);
});

$app->group('/user', function() use ($app) {
    $app->get('/register', function() use ($app) {
        $app->render('register.html');
    });
    $app->get('/login', function() use ($app) {
        $app->render('login.html');
    });
});

$app->run();

?>