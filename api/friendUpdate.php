<?php
/**
 * フレンド情報を更新する
 * PUSHのON/OFFぐらいしかない。
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'friend_id' => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
        'push_chat' => 0,
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $userToken = isset($params['user_id']) ? $params['user_id'] : null;
    $friendToken = isset($params['friend_id']) ? $params['friend_id'] : null;
    
    if(is_null($userToken)||!$userToken||is_null($friendToken)||!$friendToken) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromToken($userToken);
    $friend = $storage->User->getDataFromToken($friendToken);
    $userId = (int)$user['id'];
    $friendId = (int)$friend['id'];
    if(!$storage->UserFriend->checkFriend($userId,$friendId)) {
        throw new ErrorException();
    }
    $id = array(
        'user_id' => $userId,
        'friend_id' => $friendId,
    );
    $storage->beginTransaction();
    //UserFriendの更新
    $keys = array('push_chat');
    $values = getKeyValues($keys,$params);
    if($values) {
        $values['update_time'] = time();
        $storage->UserFriend->updatePrimaryOne($values,$id);
    }
    $storage->commit();
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}



