<?php
/**
 * フレンド解除
 */ 
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'from_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'to_id'   => 'rIzsn5DOZlVeoBrEXXNoPLLnlac8mJ',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
//json受け取り
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);
//存在チェック
$from_token = isset($params['from_id']) ? $params['from_id'] : null;
$to_token = isset($params['to_id']) ? $params['to_id'] : null;
if(is_null($from_token)||is_null($to_token)) {
    error_log('friendDelete Error: [token] is not found');
    http_response_code(400);
    exit;
}

$from = $storage->User->getDataFromToken($from_token);

if(!$from) {
    //存在しないユーザ
    error_log('friendDelete Error：this user is not found > '.$from_token);
    http_response_code(400);
    exit;
}
$to = $storage->User->getDataFromToken($to_token);
if(!$to) {
    //存在しないユーザ
    error_log('friendDelete Error：this user is not found > '.$to_token);
    http_response_code(400);
    exit;
}
$fromId = (int)$from['id'];
$toId   = (int)$to['id'];
//フレンド状態かチェック
if(!$storage->UserFriend->checkFriend($fromId,$toId)) {
    error_log('friendDelete Error：not friend > '.$fromId . '&' . $toId);
    http_response_code(400);
    exit;
}
$storage->beginTransaction();
try {
    //フレンド情報削除
    $storage->UserFriend->delete($fromId,$toId);
    //リクエスト情報取得
    $requestF = $storage->UserRequestFrom->get($fromId,$toId);
    $requestT = $storage->UserRequestFrom->get($toId,$fromId);
    //拒否状態にする
    //相手から送られてきた申請を拒否した状態に
    if($requestT) {
        $messageId = (int)$requestT['message_id'];
        $storage->UserRequestMessage->update($messageId,$toId,\library\Model_UserRequestMessage::CONDUCT_REFUSE_MESSAGE);
    } else {
        $messageId = $storage->UserRequestMessage->add($toId,\library\Model_UserRequestMessage::CONDUCT_REFUSE_MESSAGE);
    }
    $storage->UserRequestFrom->add($toId, $fromId, $messageId, \library\Model_UserRequestFrom::STATE_REFUSE, \library\Model_UserRequestFrom::DELETE_ON);
    $storage->UserRequestTo->add($fromId, $toId, $messageId, \library\Model_UserRequestFrom::STATE_REFUSE, \library\Model_UserRequestFrom::DELETE_ON);

    //こちらから送った申請を取り消し状態に
    if($requestF) {
        $messageId = (int)$requestF['message_id'];
        if((int)$requestF['state'] === \library\Model_UserRequestFrom::STATE_REFUSE) {
            //こちらが申請を拒否していた場合はそちらを優先する
            $storage->UserRequestMessage->update($messageId,$fromId,\library\Model_UserRequestMessage::CONDUCT_REFUSE_MESSAGE);
            $storage->UserRequestFrom->add($fromId, $toId, $messageId, \library\Model_UserRequestFrom::STATE_REFUSE, \library\Model_UserRequestFrom::DELETE_ON);
            $storage->UserRequestTo->add($toId, $fromId, $messageId, \library\Model_UserRequestFrom::STATE_REFUSE, \library\Model_UserRequestFrom::DELETE_ON);
        } else {
            //こちらの申請は取り消し状態
            $storage->UserRequestMessage->update($messageId,$fromId,\library\Model_UserRequestMessage::CONDUCT_CANCELL_MESSAGE);
            $storage->UserRequestFrom->add($fromId, $toId, $messageId, \library\Model_UserRequestFrom::STATE_CANCELL, \library\Model_UserRequestFrom::DELETE_ON);
            $storage->UserRequestTo->add($toId, $fromId, $messageId, \library\Model_UserRequestFrom::STATE_CANCELL, \library\Model_UserRequestFrom::DELETE_ON);
        }
    } else {
        //未登録
        $messageId = $storage->UserRequestMessage->add($fromId,\library\Model_UserRequestMessage::CONDUCT_CANCELL_MESSAGE);
        //こちらからの申請は取り消し状態に。
        $storage->UserRequestFrom->add($fromId, $toId, $messageId, \library\Model_UserRequestFrom::STATE_CANCELL, \library\Model_UserRequestFrom::DELETE_ON);
        $storage->UserRequestTo->add($toId, $fromId, $messageId, \library\Model_UserRequestFrom::STATE_CANCELL, \library\Model_UserRequestFrom::DELETE_ON);
    }
} catch (Exception $ex) {
    error_log($e->getMessage());
    $storage->rollback();
    http_response_code(400);
    exit;
}
$storage->commit();
http_response_code(200);
