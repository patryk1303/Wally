<?php

/**
 * checks message creaton form POST data
 * @param array $data mesage creaton form POST data
 * @param int $groupId group id
 * @return array(bool,int) true and new post id if correct, false and -1 when not
 */
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

/**
 * stores new message in database
 * @param string $_content message body
 * @param int $_userId message's author id
 * @param int $_priority message priority
 * @param int $_groupId message's group id
 * @return int new message id
 */
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

/**
 * edits message
 * @param int $_messageId message id to edit
 * @param string $_message new message content
 * @param int $_priority new message priority
 * @return int message id
 */
function editPost($_messageId,$_message,$_priority) {
    $post = R::load('posts', $_messageId);
    $post->message = $_message;
    $post->priority = $_priority;
    return R::store($post);
}

/**
 * gets JSON messages from group
 * @param int $_groupId group id
 * @return string messages from group JSON data
 */
function getPosts($_groupId) {
    $posts = R::find('posts', 'group_id = :group_id ORDER BY priority DESC, id DESC', array(':group_id'=>$_groupId));
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

/**
 * gets JSON message
 * @param int $postId message id
 * @return string message JSON data
 */
function getPost($postId) {
    $post = R::load('posts',$postId);
    $JSONreturn = array(
        "id"    =>  $post->id,
        "message"=>  $post->message,
        "priority"  =>  $post->priority
    );
    return json_encode($JSONreturn);
}

/**
 * gest JSON data for latest user's group posts
 * @param int $_userId user id
 * @return string latests user's messages JSON data
 */
function getLatestsPosts($_userId) {
    $posts = R::getAll("Select posts.message, posts.user_id, users.first_name, users.last_name, posts.posted_time, groups.name, groups.id As group_id From posts Inner Join groups On groups.id = posts.group_id Inner Join groupmembers On groupmembers.group_id = groups.id Inner Join users On posts.user_id = users.id And groupmembers.member_id = users.id Where posts.user_id = $_userId Limit 10");
    return json_encode($posts);
}

/**
 * deletes message when is was created by current logged in user
 * @param type $postId posts id to delete
 */
function deletePost($postId) {
    $post = R::load('posts',$postId);
    if($post->user_id == $_SESSION['user_id']) {
        R::trash( $post );
    }
}