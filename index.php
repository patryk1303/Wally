<?php

session_cache_limiter(false);
session_start();

require_once 'config.php';
require_once 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
require_once 'Smarty/Smarty.class.php';
require_once 'Smarty.php';

require_once 'lib/rb.php';
R::setup('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
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

/**
 * runs refore each route match.
 * If user is logged - loads it's data and passes to tamplate engine
 */
$app->hook('slim.before.dispatch', function() use ($view) {
    if($_SESSION['login_ok']) {
        $user = R::load('users',$_SESSION['user_id']);
        $userGroups = R::getAll("SELECT * FROM users,groups WHERE users.id = groups.owner");
        $view->getInstance()->assign('user',$user);
        $view->getInstance()->assign('hash',md5($user->email));
        $view->getInstance()->assign('userGroups',$userGroups);
    }
});

/**
 * matches main page
 */
$app->get('/', function() use ($app) {
    
    if($_SESSION['login_ok']) {
        $app->render('main.html');
    } else {    
        $app->render('main.html');
    }
});

/**
 * matches GET contact page
 */
$app->get('/contact', function() use ($app) {
    $app->render('contact.html');
});
/**
 * POST contact page - checks form validity and sends mail
 * to development team
 */
$app->post('/contact', function() use ($app) {
    $postData = $app->request->post();
    $correct = checkcontact($postData);
    
    $arr = array(
        'correct'   =>  $correct
    );
    $app->render('contact_post.html', $arr);
});

/**
 * matches routes fror /user/*
 */
$app->group('/user', function() use ($app) {
    /**
     * when user isn't logged - displays register form
     */
    $app->get('/register', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->redirect('./..');
        } else {
            $app->render('user/register.html');
        }
    });
    /**
     * checks and stores new user data in database
     */
    $app->post('/register', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->redirect ('./..');
        }
        
        $postData = $app->request->post();
        $data = CheckUserRegister($postData);
        
        $app->render('user/register_post.html', array('correct'=>$data[0], 'errors'=>$data[1]));
    });
    /**
     * when user isn't logged - displays login form
     */
    $app->get('/login', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->redirect ('./..');
        }
        $app->render('user/login.html');
    });
    /**
     * checks if user put correct login data, if correct - logges in
     */
    $app->post('/login', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->redirect ('./..');
        }
        $postData = $app->request->post();
        $logged_ok = CheckUserLogin($postData);
        
        $app->render('user/login_post.html', array('logged_ok'=>$logged_ok));
    });
    /**
     * retrieves user data and displays them,
     * when user is not logged - display warning
     * @param $id - user id
     */
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
    /**
     * matches logout
     */
    $app->get('/logout', function() use ($app) {
        destroySession();
        $app->redirect('./..');
    });
    /**
     * displays user menage form
     */
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
    /**
     * changes current user data in database
     */
    $app->post('/menage', function() use ($app) {
        if($_SESSION['login_ok']) {
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
        } else {
            $app->render('common/login_required.html');
        }
    });
});

/**
 * matches routing for groups
 */
$app->group('/group', function() use ($app) {
    
    /**
     * reditects to /group/list
     */
    $app->get('/', function() use($app) {
        $app->redirect('./..');
    });
    
    /**
     * displays group create form
     */
    $app->get('/create', function() use ($app) {
        if($_SESSION['login_ok']) {
            $app->render('group/create.html');
        } else {
            $app->render('common/login_required.html');
        }
    });
    /**
     * creates group when POST data is correct
     */
    $app->post('/create', function() use ($app) {
        $postData = $app->request()->post();
        $data = CheckGroupCreation($postData);
        $correct = $data[0];
        $gID = $data[1];
        
        $app->render('group/create_post.html', array('correct'=>$correct,'id'=>$gID));
    });
    
    /**
     * displays group
     * @param id group id
     */
    $app->get('/view/:id', function($id) use ($app) {
        
        if($_SESSION['login_ok']) {
            $posts = R::getAll("SELECT * FROM posts,groups WHERE group_id = group.id");
            $group = R::load('groups',$id);
            $members = R::getAll("Select users.id,users.first_name,users.last_name From groups Inner Join groupmembers On groups.id = groupmembers.group_id Inner Join users On users.id = groupmembers.member_id WHERE groups.id = :group_id",
                        [':group_id' => $id]
                       );

            $app->render('group/view.html',array("posts" => $posts, "group" => $group, "members" => $members));
        } else {
            $app->render('common/login_required.html');
        }
        
    });
    
    /**
     * add message to group when data is corrent
     * @param groupId group id
     */
    $app->post('/post/:id', function($groupId) use ($app) {
        $postData = $app->request()->post();
        print_r($postData);
        $data = CheckPostAdd($postData,$groupId);
        $correct = $data[0];
        $gID = $data[1];
        
        $app->redirect("./../view/$groupId");
    });
});


//section for JSON responses
$app->group('/posts', function() use ($app) {
    /**
     * displays message
     * @param id message id
     */
    $app->get('/get-post/:id', function($id) use ($app) {
        echo getPost($id);
    });
    /**
     * displays latests 10 posts from user groups as JSON data
     */
    $app->get('/get-all-latest-posts', function() use ($app) {
        echo getLatestsPosts($_SESSION['user_id']);
    });
    /**
     * displays messages from group JSON data
     * @param id group id
     */
    $app->get('/get-posts-from-group/:groupId', function($groupId) use ($app) {
        $posts = getPosts($groupId);
        echo $posts;
    });
    /**
     * removes message
     * @param id message id
     */
    $app->post('/remove/:id', function($id) use ($app) {
        deletePost($id);
    });
});

$app->run();