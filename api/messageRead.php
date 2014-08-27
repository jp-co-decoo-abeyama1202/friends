<?php
/**
 * メッセージの既読化
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'friend_id' => 'EgdbaamiMtkr85SvlorzyvsvDuk00m',
        'ids' => array(6),
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
$ids = isset($params['ids']) ? $params['ids'] : array();
if(is_null($token)||is_null($ftoken)) {
    error_log('messageRead Error:[user_id] or [friend_id] is not found');
    http_response_code(400);
    exit;
}
if(!$ids) {
    error_log('messageRead Error:[ids] not found');
    http_response_code(400);
    exit;
}

$user = $storage->User->getDataFromToken($token);
$friend = $storage->User->getDataFromToken($ftoken);
if(!$user) {
    //存在しないユーザ
    error_log('messageRead Error：this user is not found > '.$token);
    http_response_code(400);
    exit;
}
if(!$friend) {
    //存在しないユーザ
    error_log('messageRead Error：this user is not found > '.$ftoken);
    http_response_code(400);
    exit;
}

$id = (int)$user['id'];
$friendId = (int)$friend['id'];
//フレンド紐付ID取得
$friendsId = $storage->Friends->getId($id,$friendId);
if(!$friendsId) {
    error_log('messagePost Error：not friend > '.$id.'&'.$friendId);
    http_response_code(400);
    exit;
}

//フレンド状態かチェック
if(!$storage->UserFriend->checkFriend($id,$friendId)) {
    error_log('messageRead Error：not friend > '.$id.'&'.$friendId);
    http_response_code(400);
    exit;
}

//更新するIDだけ引っ張り出す
$messages = $storage->Message->getMessageFromIds($friendsId,$ids);
$ids_ = array();
foreach($messages as $message) {
    if((int)$message['read_flag'] === \library\Model_Message::READ_OFF) {
        $ids_[] = (int)$message['id'];
    }
}
if(!$ids_) {
    http_response_code(200);
    exit();
}

//既読にする
$storage->beginTransaction();
try{
    $message = $storage->Message->read($friendsId,$ids_);
} catch (Exception $e) {
    error_log($e->getMessage());
    var_dump($e->getMessage());
    $storage->rollback();
    http_response_code(400);
    exit;
}
$storage->commit();
http_response_code(200);