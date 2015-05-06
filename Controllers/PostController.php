<?php

function CheckPostAdd($data,$groupId) {

    $message = $data['message'];
    $priority = $data['priority'];
    
    $correct = array(
        'message'      => 0,
        'priority'      => 0,
    );
    
    if(strlen($message)>0) {
        $correct['message'] = 1;
        $correct['priority'] = 1;
    }

    $c = count(array_unique($correct));
    if ($c == 1) {
        if ($correct['message'] == 1 && $correct['priority'] == 1) {
            //TODO check if session.user_id was not manipulated
            $id = addPost($message,$_SESSION['user_id'],$priority,$groupId);
            return array(true,$id);
        }
        return array(false,-1);
    }
    
    return array(false,-1);
}

function addPost($_content, $_userId, $_priority , $_groupId) {
    $post = R::dispense('posts');
    $post->message = $_content;
    $post->user_id = $_userId;
    $post->priority = $_priority;
    $post->group_id = $_groupId;
    $id = R::store($post);
    
    return $id;
}