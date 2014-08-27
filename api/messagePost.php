<?php
/**
 * メッセージ送信
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'friend_id' => 'EgdbaamiMtkr85SvlorzyvsvDuk00m',
        'message' => 'meeeeeeeeeeeeeeeee',
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
$message = isset($params['message']) ? $params['message'] : null;

if(is_null($token)||is_null($ftoken)) {
    error_log('messagePost Error:[user_id] or [friend_id] is not found');
    http_response_code(400);
    exit;
}
if(is_null($message)) {
    error_log('messagePost Error:[message] not found');
    http_response_code(400);
    exit;
}

$user = $storage->User->getDataFromToken($token);
$friend = $storage->User->getDataFromToken($ftoken);
if(!$user) {
    //存在しないユーザ
    error_log('messagePost Error：this user is not found > '.$token);
    http_response_code(400);
    exit;
}
if(!$friend) {
    //存在しないユーザ
    error_log('messagePost Error：this user is not found > '.$ftoken);
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
    error_log('messagePost Error：not friend > '.$id.'&'.$friendId);
    http_response_code(400);
    exit;
}

//メッセージを送信する
$storage->beginTransaction();
try{
    $ret = $storage->Message->add($friendsId,$id,$message);
    //TO側にPUSH通知
    $option = $storage->UserOption->primaryOne($friendId);
    if($option && $option['push_friend'] == \library\Model_UserOption::FLAG_ON && $friend['push_id']) {
        //PUSH送信
        $device = $friend['device'] == \library\Model_User::DEVICE_IOS ? \library\Push::TYPE_IOS : \library\Push::TYPE_ANDROID;
        $bool = $push->send(
            $device,
            $friend['push_id'],
            $push->message($user['name'].'さんからメッセージが届きました')
            ->badge(3)
            ->sound(null)
        );
        $values = array(
            'from_id' => $id,
            'to_id' => $friendId,
            'type' => \library\Model_Push::TYPE_REQUEST,
            'result' => $bool ? \library\Model_Push::RESULT_SUCCESS : \library\Model_Push::RESULT_FAILED,
            'create_time' => time()
        );
        $storage->Push->insertOne($values);
    }
    
} catch (Exception $ex) {
    error_log('messagePost Error：not friend > '.$id.'&'.$friendId);
    $storage->rollback();
    http_response_code(400);
    exit;
}
if(!$ret) {
    error_log('messagePost Error：insert failed > '.$id.'&'.$friendId);
    $storage->rollback();
    http_response_code(400);
    exit;
}
$storage->commit();
http_response_code(200);