<?php

function CheckUserRegister($data) {
    $email = $data['email'];
    $passwd1 = md5($data['passwd1']);
    $passwd2 = md5($data['passwd2']);
    $name = $data['name'];
    $surname = $data['surname'];
    $skype = $data['skype'];
    $phone = $data['phone'];
    
    $correct = array(
        'email'     => 0,
        'password'  => 0,
        'name'      => 1,
        'surname'   => 1
    );
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $correct['email'] = 1;
    }
    if ($passwd1 == $passwd2) {
        $correct['password'] = 1;
    }

    $c = count(array_unique($correct));
    if ($c == 1) {
        if ($correct['email'] == 1) {
            registerUser($email,$passwd1,$name,$surname,$skype,$phone);
            return true;
        }
        return false;
    }
    
    return false;
}

function registerUser($email,$passwd,$name,$surname,$skype,$phone) {
    $user = R::dispense('users');
    $user->email = $email;
    $user->password = $passwd;
    $user->first_name = $name;
    $user->last_name = $surname;
    $user->skype = $skype;
    $user->phone_number = $phone;
    $user->setMeta("buildcommand.unique" , array(array($email)));
    
    $id = R::store($user);
}

function CheckUserLogin($data) {
    $email = $data['email'];
    $passwd = md5($data['passwd']);
    
    $user = R::find('users', "email LIKE :email AND password LIKE :passwd",
                   array(
                   ':email' => $email,
                   ':passwd' => $passwd
                    )
                   );
    
//    echo "c: ".count($user)."<br>";
    
    if(count($user) == 1) {
        $_SESSION['user_id'] = $user[1]->id;
        $_SESSION['email'] = $user[1]->email;
        $_SESSION['password'] = $user[1]->password;
        $_SESSION['login_ok'] = true;
//        print_r($_SESSION);
        return true;
    }
    
    return false;
}