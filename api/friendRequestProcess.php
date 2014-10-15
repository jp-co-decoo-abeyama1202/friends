<?php
/**
 * フレンド申請のステータス変更
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        //'from_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'from_ids' =>  'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'to_id'   => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
        'state'  => \library\Model_UserRequestFrom::STATE_EXECUTE,
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
    $from_tokens = isset($params['from_ids']) ? explode(",",$params['from_ids']) : array();
    $to_token = isset($params['to_id']) ? $params['to_id'] : null;
    
    if(!is_null($from_token)&&!in_array($from_token,$from_tokens)&&$from_token) {
        $from_tokens[] = $from_token;
    }
    
    if(!$from_tokens||is_null($to_token)) {
        throw new InvalidArgumentException();
    }
    //変更するステータス
    $state  = isset($params['state']) ? (int)$params['state'] : null;
    if(!in_array($state,array(\library\Model_UserRequest::STATE_EXECUTE,\library\Model_UserRequest::STATE_REFUSE,\library\Model_UserRequest::STATE_CANCELL))) {
        throw new InvalidArgumentException();
    }
    
    $to = $storage->User->getDataFromToken($to_token);
    $toId   = (int)$to['id'];
    $fromIds = array();
    foreach($from_tokens as $from_token) {
        $from = $storage->User->getDataFromToken($from_token);
        $fromId = (int)$from['id'];
        //申請状態が存在するか
        $request = $storage->UserRequestFrom->get($fromId,$toId);
        if(!$request || (int)$request['state'] !== \library\Model_UserRequestFrom::STATE_PENDING) {
            throw new ErrorException();
        }
        $fromIds[$fromId] = $request;
    }
    
    $storage->beginTransaction();
    foreach($fromIds as $fromId => $request) {
        //ステータス更新
        if($state === \library\Model_UserRequest::STATE_EXECUTE) {
            //フレンド成立
            $messageId = (int)$request['message_id'];
            $storage->UserRequest->executeRequest($fromId,$toId,$messageId);
            //PushQueueに登録
            $storage->PushQueue->add($toId,$fromId,\library\Model_Push::TYPE_FRIEND);
        } else {
            //それ以外
            $storage->UserRequest->updateRequest($fromId,$toId,$state);
        }
    }
    $storage->commit();
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}

