<?php

session_cache_limiter(false);
session_start();

require_once 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
require_once 'Smarty/Smarty.class.php';
require_once 'Smarty.php';

require_once 'lib/rb.php';
R::setup('mysql:host=localhost;dbname=Wally', 'root', '');
require_once 'Controllers/RegisterControllers.php';
require_once 'lib/sessions.php';
require_once 'lib/headers.php';

$config = array(
    'view' => new \Slim\Views\Smarty(),
    'debug' => true,
    'templates.path' => './templates'
);

$app = new \Slim\Slim($config);

checkSessionVars();
checkUserSession();

$view = $app->view();
$view->parserDirectory = dirname(__FILE__) . '/templates';

$plug = dirname(__FILE__) . '/SmartyPlugins';
$view->parserExtensions = array($plug);

$app->setName('Wally');

$app->hook('slim.before.dispatch', function() use ($view) {
    if($_SESSION['login_ok']) {
        $user = R::load('users',$_SESSION['user_id']);
        $userGroups = R::getAll("SELECT * FROM users,groups WHERE users.id = groups.owner");
        $view->getInstance()->assign('user',$user);
        $view->getInstance()->assign('hash',md5($user->email));
        $view->getInstance()->assign('userGroups',$userGroups);
    }
});

$app->get('/', function() use ($app) {
    
    if($_SESSION['login_ok']) {
        //TODO download latest posts (eg. 10) from user's groups and store them
        //to variable to use in array ['posts'] in $app->render()
        
        $app->render('main.html',array('posts' => []));
    } else {    
        $app->render('main.html');
    }
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
        if($_SESSION['login_ok']) {
            $app->redirect('./..');
        } else {
            $app->render('user/register.html');
        }
    });
    $app->post('/register', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->redirect ('./..');
        }
        
        $postData = $app->request->post();
        $data = CheckUserRegister($postData);
        
        $app->render('user/register_post.html', array('correct'=>$data[0], 'errors'=>$data[1]));
    });
    $app->get('/login', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->redirect ('./..');
        }
        $app->render('user/login.html');
    });
    $app->post('/login', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->redirect ('./..');
        }
        $postData = $app->request->post();
        $logged_ok = CheckUserLogin($postData);
        
        $app->render('user/login_post.html', array('logged_ok'=>$logged_ok));
    });
    $app->get('/profile/:id', function($id) use ($app) {
        $user = R::findOne('users', "id = $id");
        
        if($_SESSION['login_ok'] && $user) {
            $data = array(
                'name'     => $user->first_name,
                'surname'  => $user->last_name,
                'email'    => $user->email,
                'hemail'   => md5($user->email),
                'skype'    => $user->skype,
                'tel'      => $user->phone_number
            );
            $app->render('user/view_profile.html', $data);
        } elseif($_SESSION['login_ok'] && !$user) {
            $app->render('user/user_not_exist.html');
        } else {
            $app->render('common/login_required.html');
        }
    });
    $app->get('/logout', function() use ($app) {
        destroySession();
        $app->redirect('./..');
    });
    $app->get('/menage', function() use($app) {
        $user = R::findOne('users', "id = " . $_SESSION['user_id']);

        if($_SESSION['login_ok'] && $user) {
            $data = array(
                'name'     => $user->first_name,
                'surname'  => $user->last_name,
                'email'    => $user->email,
                'skype'    => $user->skype,
                'tel'      => $user->phone_number
            );
            $app->render('user/edit_profile.html', $data);
        } elseif($_SESSION['login_ok'] && !$user) {
            $app->render('user/user_not_exist.html');
        } else {
            $app->render('common/login_required.html');
        }
    });
    $app->post('/menage', function() use ($app) {
        $newName = $app->request->post('name');
        $newSurName = $app->request->post('surname');
        $newEmail = $app->request->post('email');
        $newSkype = $app->request->post('skype');
        $newTel = $app->request->post('tel');
        $uID = $_SESSION['user_id'];
        
        $user = R::load('users',$uID);
        
        $user->first_name = $newName;
        $user->last_name = $newSurName;
        $user->email = $newEmail;
        $user->skype = $newSkype;
        $user->phone_number = $newTel;
        
        R::store($user);
        
        $app->redirect("./profile/$uID");
    });
});

$app->group('/group', function() use ($app) {
    
    $app->get('/', function() use($app) {
        $app->redirect('./list');
    });
    
    $app->get('/create', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->render('group/create.html');
        } else {
            $app->render('common/login_required.html');
        }
    });
    $app->post('/create', function() use ($app) {
        $postData = $app->request()->post();
        $data = CheckGroupCreation($postData);
        $correct = $data[0];
        $gID = $data[1];
        
        $app->render('group/create_post.html', array('correct'=>$correct,'id'=>$gID));
    });
    
    $app->get('/view/:id', function($id) use ($app) {
        
        if($_SESSION['login_ok']) {
            $posts = R::getAll("SELECT * FROM posts,groups WHERE group_id = group.id");
            $group = R::load('groups',$id);

            $app->render('group/view.html',array("posts" => $posts, "group" => $group));
        } else {
            $app->render('common/login_required.html');
        }
        
    });
    
    $app->post('/post/:id', function($groupId) use ($app) {
        $postData = $app->request()->post();
        $data = CheckPostAdd($postData,$groupId);
        $correct = $data[0];
        $gID = $data[1];
        
        $app->redirect("./../view/$groupId");
    });
});


//section for JSON responses
$app->group('/posts', function() use ($app) {
    $app->get('/get-all-latest-posts', function() use ($app) {
        echo 'laj,laj,laj';
    });
    $app->get('/get-posts-from-group/:groupId', function($groupId) use ($app) {
//        JSONheader();
        $posts = getPosts($groupId);
        echo $posts;
    });
});

$app->run();