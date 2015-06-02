<?php

/**
 * checks if contact form was valid
 * @param array $request contact form POST params
 * @return boolean true when contact form is correct, false - then not
 */
function checkContact($request) {
    $name = $request['name'];
    $surname = $request['surname'];
    $email = $request['email'];
    $content = $request['content'];

    $correct = array(
        'name'      =>  false,
        'surname'   =>  false,
        'email'     =>  false,
        'content'   =>  false
    );

    if(filter_var($email, FILTER_VALIDATE_EMAIL))
        $correct['email'] = true;
    if(strlen($name) > 0) $correct['name'] = true;
    if(strlen($surname) > 0) $correct['surname'] = true;
    if(strlen($content) > 0) $correct['content'] = true;

    if(count(array_unique($correct))) {
        return true;
    }
    
    return false;
}

?>