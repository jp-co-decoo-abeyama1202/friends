<?php
/**
 * 最新メッセージ一覧&未読件数
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //json受け取り
    //$json = filter_input(INPUT_POST,'params');
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    if(!$json) {
        throw new \library\NotParamsException();
    }
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    if(is_null($token)) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    $ret = array(
        'user_id' => $token,
        'no_read' => 0,
        'request' => 0,
        'list' => array(),
    );
    $list = array();
    //最新メッセージを取得する
    //フレンド情報取得
    list($messages,$noreadF) = $storage->Message->getNewMessages($id);
    foreach($messages as $message) {
        $message['type'] = 'friend';
        array_push($list,$message);
    }
    //グループのメッセージ
    list($gMessages,$noreadG) = $storage->GroupMessage->getNewMessage($id);
    foreach($gMessages as $gMessage) {
        $gMessage['type'] = 'group';
        //array_push($list,$gMessage);
    }
    //未読数
    usort($list,'newlistsort');
    $ret['list'] = $list;
    $ret['no_read'] = $noreadF + $noreadG;
    $ret['request'] = $storage->UserRequestTo->getPendingCounts($id);
    return \library\Response::json($ret);
} catch (Exception $e) {
    return \library\Response::error($e);
}