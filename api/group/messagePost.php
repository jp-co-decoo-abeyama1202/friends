<?php
/**
 * グループにメッセージを送信する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'group_id' => 20,
        'message' => 'てすとぉぉぉぉぉ！',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);

    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $groupId = isset($params['group_id']) ? (int)$params['group_id'] : null;
    $message = isset($params['message']) ? $params['message'] : null;
    if(is_null($token)||is_null($groupId)||!$token||!$groupId||!$message) {
        throw new InvalidArgumentException();
    }
    
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    //グループ取得 存在しなければException
    $group = $storage->Group->getGroupOrFail($groupId);
    //グループメンバーか？
    if(!$storage->Group->checkUser($groupId,$id)) {
        throw new ErrorException();
    }
    //メッセージ送信
    $storage->beginTransaction();
    $storage->Group->addMessage($groupId,$id,$message);
    $storage->PushQueue->add($userId,$groupId,\library\Model_Push::TYPE_GROUP_MESSAGE,$message);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}