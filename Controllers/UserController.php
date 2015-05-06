<?php

/**
 * Checks if user registration data are correct
 * @param array $data POST data from register form
 * @return boolean true where data are correct
 */
function CheckUserRegister($data) {
    $email = $data['email'];
    $passwd1 = md5($data['passwd1']);
    $passwd2 = md5($data['passwd2']);
    $name = $data['name'];
    $surname = $data['surname'];
    $skype = $data['skype'];
    $phone = $data['phone'];
    
    $errors = array();
    
    if($passwd1!=$passwd2) {
        $errors[] = "Hasła są różne";
    }
    
    if(strlen($passwd1) < 8) {
        $errors[] = "Hasło musi mieć 8 znaków lub więcej";
    }
    
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
    if ($c == 1 && count($errors) == 0) {
        if ($correct['email'] == 1) {
            
            $emails = R::find('users', "email LIKE :email", array(":email" => $email));
            
            if (count($emails) > 0) {
                $errors[] = "Taki użytkownik już istnieje";
                return array(false, $errors);
            }
            
            registerUser($email,$passwd1,$name,$surname,$skype,$phone);
            return array(true, array());
        }
        return array(false, $errors);
    }
    
    return array(false, $errors);
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
                   array(':email' => $email,':passwd' => $passwd));
    
//    echo "c: ".count($user)."<br>";
//    print_r($user);
    
    if(count($user) == 1) {
        $user = R::findOne('users', "email LIKE :email AND password LIKE :passwd",
                   array(':email' => $email,':passwd' => $passwd));
        $_SESSION['user_id'] = $user->id;
        $_SESSION['first_name'] = $user->first_name;
        $_SESSION['last_name'] = $user->last_name;
        $_SESSION['email'] = $user->email;
        $_SESSION['password'] = $user->password;
        $_SESSION['login_ok'] = true;
        return true;
    }
    
    return false;
}