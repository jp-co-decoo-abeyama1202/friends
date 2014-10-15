<?php
/**
 * フレンド申請
 * Created by PhpStorm.
 * User: ishii
 * Date: 14/07/29
 * Time: 13:17
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'from_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'to_id'   => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
        'text' => '申請よろしくお願いします'
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $from_token = isset($params['from_id']) ? $params['from_id'] : null;
    $to_token = isset($params['to_id']) ? $params['to_id'] : null;
    if(is_null($from_token)||is_null($to_token)) {
        throw new InvalidArgumentException();
    }
    $from = $storage->User->getDataFromToken($from_token);
    $to = $storage->User->getDataFromToken($to_token);
    $fromId = (int)$from['id'];
    $toId   = (int)$to['id'];
    $text   = isset($params['text']) ? $params['text'] : '';
    $state  = \library\Model_UserRequestFrom::STATE_PENDING;
    //ブロック状態確認
    if($storage->UserBlock->check($fromId,$toId,true)) {
        //どちらかがブロック中
        throw new ErrorException();
    }
    $storage->beginTransaction();
    //登録
    $storage->UserRequest->addRequest($fromId,$toId,$text);
    //TO側にPUSH通知
    $storage->PushQueue->add($fromId,$toId,\library\Model_Push::TYPE_REQUEST,$text);
    $storage->commit();
    
    return \library\Response::success();
}catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}
