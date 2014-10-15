<?php
/**
 * メッセージの既読化
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
        'friend_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'ids' => array(1),
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $ftoken = isset($params['friend_id']) ? $params['friend_id'] : null;
    $ids = isset($params['ids']) ? $params['ids'] : array();
    if(is_null($token)||is_null($ftoken)||!$ids) {
        throw new InvalidArgumentException();
    }

    $user = $storage->User->getDataFromToken($token);
    $friend = $storage->User->getDataFromToken($ftoken);

    $id = (int)$user['id'];
    $friendId = (int)$friend['id'];
    //フレンド紐付ID取得
    $friendsId = $storage->Friends->getId($id,$friendId);
    if(!$friendsId) {
        throw new ErrorException();
    }
    //フレンド状態かチェック
    if(!$storage->UserFriend->checkFriend($id,$friendId)) {
        throw new ErrorException();
    }
    //更新するIDだけ引っ張り出す
    $messages = $storage->Message->getMessageFromIds($friendsId,$ids);
    $ids_ = array();
    foreach($messages as $message) {
        if((int)$message['read_flag'] === \library\Model_Message::READ_OFF && $message['sender_id'] != $id) {
            $ids_[] = (int)$message['id'];
        }
    }
    if(!$ids_) {
        throw new BadMethodCallException();
    }
    //既読にする
    $storage->beginTransaction();
    $message = $storage->Message->read($friendsId,$ids_);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}