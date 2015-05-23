<?php

function CheckPostAdd($data,$groupId) {
    $message = $data['message'];
    $priority = $data['priority'];
    $messageId = $data['postID'];
    
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
            if($messageId == -1) {
                $id = addPost($message,$_SESSION['user_id'],$priority,$groupId);
            } else {
                $id = editPost($messageId,$message,$priority);
            }
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

function editPost($_messageId,$_message,$_priority) {
    $post = R::load('posts', $_messageId);
    $post->message = $_message;
    $post->priority = $_priority;
    return R::store($post);
}

function getPosts($_groupId) {
    $posts = R::find('posts', 'group_id = :group_id ORDER BY id DESC', array(':group_id'=>$_groupId));
    $JSONreturn = array();
    
    foreach($posts as $post) {
        $user = R::load('users', $post->user_id);
        $JSONreturn[] = array(
            "id"    =>  $post->id,
            "message"=>  $post->message,
            "priority"  =>  $post->priority,
            "user_name" =>  $user->first_name . ' ' . $user->last_name,
            "posted_time"   =>  $post->postedTime,
            "user_id"   =>  $user->id
        );
    }
    
    return json_encode($JSONreturn);
}

function getPost($postId) {
    $post = R::load('posts',$postId);
    $JSONreturn = array(
        "id"    =>  $post->id,
        "message"=>  $post->message,
        "priority"  =>  $post->priority
    );
    return json_encode($JSONreturn);
}

function getLatestsPosts($_userId) {
    $posts = R::getAll("Select posts.message, posts.user_id, users.first_name, users.last_name, posts.posted_time, groups.name, groups.id As group_id From posts Inner Join groups On groups.id = posts.group_id Inner Join groupmembers On groupmembers.group_id = groups.id Inner Join users On posts.user_id = users.id And groupmembers.member_id = users.id Where posts.user_id = $_userId Limit 10");
    return json_encode($posts);
}