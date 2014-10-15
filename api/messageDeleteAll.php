<?php
/**
 * メッセージの全削除
 * 削除は論理削除を行う(delete_flag = 1)
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'friend_id' => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
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
    if(is_null($token)||is_null($ftoken)) {
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
    //全削除状態にする
    $storage->beginTransaction();
    $message = $storage->Message->deleteAll($friendsId);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}
