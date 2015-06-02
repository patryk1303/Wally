<?php

/**
 * checks SESSION values if they're set.
 * When calues is not set - set to 0
 */
function checkSessionVars() {
    if(!isset($_SESSION['user_id'])) {
        $_SESSION['user_id']  = 0;
    }
    if(!isset($_SESSION['email'])) {
        $_SESSION['email']    = 0;
    }
    if(!isset($_SESSION['password'])) {
        $_SESSION['password'] = 0;
    }
    if(!isset($_SESSION['login_ok'])) {
        $_SESSION['login_ok'] = 0;
    }
}

/**
 * destroys current SESSION
 */
function destroySession() {
    unset($_SESSION['user_id']);
    unset($_SESSION['email']);
    unset($_SESSION['password']);
    unset($_SESSION['login_ok']);
};

/**
 * checks if SESSION data is ok
 * @return boolean true when SESSION data is ok, false when not
 */
function checkUserSession() {
    $user_id = $_SESSION['user_id'];
    $email   = $_SESSION['email'];
    $passwd  = $_SESSION['password'];

    $user = R::find('users', "id = :id AND email LIKE :email AND password LIKE :passwd",
                    array(':id' => $user_id,':email'  => $email,':passwd' => $passwd));

    if(count($user) == 1) {
        $user = R::findOne('users', "id = :id AND email LIKE :email AND password LIKE :passwd",
                    array(':id' => $user_id,':email'  => $email,':passwd' => $passwd));
        $_SESSION['user_id'] = $user->id;
        $_SESSION['email'] = $user->email;
        $_SESSION['password'] = $user->password;
        $_SESSION['login_ok'] = true;
        return true;
    }

    return false;
}