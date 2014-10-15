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
try {
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $ftoken = isset($params['friend_id']) ? $params['friend_id'] : null;
    $message = isset($params['message']) ? $params['message'] : null;
    if(is_null($token)||is_null($ftoken)||!$message) {
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

    //メッセージを送信する
    $storage->beginTransaction();
    $storage->Message->add($friendsId,$id,$message);
    //Queueに登録
    $storage->PushQueue->add($id,$friendId,\library\Model_Push::TYPE_MESSAGE,$message);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}