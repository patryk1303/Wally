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
    $post->postedTime = R::isoDateTime();
    $post->group_id = $_groupId;
    $id = R::store($post);
    
    return $id;
}

function getPosts($_groupId) {
    $posts = R::find('posts', 'group_id = :group_id ORDER BY id DESC', array(':group_id'=>$_groupId));
    $JSONreturn = array();
    
    foreach($posts as $post) {
//        print_r($post);
        
        $user = R::load('users', $post->user_id);
        $JSONreturn[] = array(
            "id"    =>  $post->id,
            "mesage"=>  $post->message,
            "priority"  =>  $post->priority,
            "user_name" =>  $user->first_name . ' ' . $user->last_name,
            "posted_time"   =>  $post->postedTime,
            "user_id"   =>  $user->id
        );
    }
    
    return json_encode($JSONreturn);
}