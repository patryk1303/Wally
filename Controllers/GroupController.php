<?php

function CheckGroupCreation($data) {
    $name = $data['name'];
    
    $correct = array(
        'name'      => 0,
    );
    
    if(strlen($name)>0) {
        $correct['name'] = 1;
    }

    $c = count(array_unique($correct));
    if ($c == 1) {
        if ($correct['name'] == 1) {
            //TODO check if session.user_id was not manipulated
            $id = createGroup($name,$_SESSION['user_id']);
            return array(true,$id);
        }
        return array(false,-1);
    }
    
    return array(false,-1);
}

function createGroup($_name,$_owner) {
    $group = R::dispense('groups');
    $group->name = $_name;
    $group->owner = $_owner;
    $id = R::store($group);
    
    return $id;
}