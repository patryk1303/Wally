<?php

/**
 * checks group creaton form POST data
 * @param array $data group creaton form POST data
 * @return array(bool,int) true and new group id if correct, false and -1 when not
 */
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

/**
 * creates group and stores it in database
 * @param string $_name group name
 * @param int $_owner group owner
 * @return int new group id
 */
function createGroup($_name,$_owner) {
    $group = R::dispense('groups');
    $group->name = $_name;
    $group->owner = $_owner;
    $id = R::store($group);
    
    $groupMembers = R::dispense('groupmembers');
    $groupMembers->member_id = $_SESSION['user_id'];
    $groupMembers->group_id = $id;
    R::store($groupMembers);
    
    return $id;
}