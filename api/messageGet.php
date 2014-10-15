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
try {
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $ftoken = isset($params['friend_id']) ? $params['friend_id'] : null;
    $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
    $count = isset($params['count']) ? (int)$params['count'] : \library\Model_Message::DEFAULT_COUNT;
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
    /*
    //フレンド状態かチェック
    if(!$storage->UserFriend->checkFriend($id,$friendId)) {
        error_log("user_id = $token");
        error_log("friend_id = $ftoken");
        throw new ErrorException();
    }
     */

    //メッセージを取得する
    $message = $storage->Message->getMessage($friendsId,$offset,$count);
    foreach($message as $key => $m) {
        $sId = (int)$m['sender_id'];
        $m['sender'] = $id === $sId ? \library\Model_Message::SENDER_MINE : \library\Model_Message::SENDER_FRINDS;
        unset($m['sender_id']);
        $message[$key] = $m;
    }
    $data = array(
        'friends_id'=>$token,
        'user_id' => $ftoken,
        'offset' => $offset,
        'count' => count($message),
        'message'=>$message,
    );
    return \library\Response::json($data);
} catch (Exception $e) {
    return \library\Response::error($e);
}