<?php
/**
 * メッセージ取得
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'friend_id' => 'EgdbaamiMtkr85SvlorzyvsvDuk00m',
        'offset' => 0,
        'count' => 30,
    );
    $_POST['params'] = json_encode($test_params);
}
*/
//json受け取り
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);
//存在チェック
$token = isset($params['user_id']) ? $params['user_id'] : null;
$ftoken = isset($params['friend_id']) ? $params['friend_id'] : null;
$offset = isset($params['offset']) ? (int)$params['offset'] : 0;
$count = isset($params['count']) ? (int)$params['count'] : \library\Model_Message::DEFAULT_COUNT;
if(is_null($token)||is_null($ftoken)) {
    error_log('messageGet Error:[user_id] or [friend_id] is not found');
    http_response_code(400);
    exit;
}

$user = $storage->User->getDataFromToken($token);
$friend = $storage->User->getDataFromToken($ftoken);
if(!$user) {
    //存在しないユーザ
    error_log('messageGet Error：this user is not found > '.$token);
    http_response_code(400);
    exit;
}
if(!$friend) {
    //存在しないユーザ
    error_log('messageGet Error：this user is not found > '.$ftoken);
    http_response_code(400);
    exit;
}

$id = (int)$user['id'];
$friendId = (int)$friend['id'];
//フレンド紐付ID取得
$friendsId = $storage->Friends->getId($id,$friendId);
if(!$friendsId) {
    error_log('messageGet Error：not friend > '.$id.'&'.$friendId);
    http_response_code(400);
    exit;
}
//フレンド状態かチェック
if(!$storage->UserFriend->checkFriend($id,$friendId)) {
    error_log('messageGet Error：not friend > '.$id.'&'.$friendId);
    http_response_code(400);
    exit;
}

//メッセージを送信する
try{
    $message = $storage->Message->getMessage($friendsId,$offset,$count);
    foreach($message as $key => $m) {
        $sId = (int)$m['sender_id'];
        $m['sender'] = $id === $sId ? \library\Model_Message::SENDER_MINE : \library\Model_Message::SENDER_FRINDS;
        unset($m['sender_id']);
        $message[$key] = $m;
    }
} catch (Exception $ex) {
    error_log('messageGet Error：not friend > '.$id.'&'.$friendId);
    $storage->rollback();
    http_response_code(400);
    exit;
}

echo json_encode(array(
        'friends_id'=>$token,
        'user_id' => $ftoken,
        'offset' => $offset,
        'count' => count($message),
        'message'=>$message,
    )
);
http_response_code(200);