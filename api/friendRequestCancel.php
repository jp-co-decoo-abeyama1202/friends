<?php
/**
 * フレンド申請のキャンセル
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'from_id' =>  'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'to_ids'   => 'imliL6n4jQBiGhhyKxGEWXYvOF9Don',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $from_token = isset($params['from_id']) ? $params['from_id'] : null;
    $to_tokens = isset($params['to_ids']) ? explode(',',$params['to_ids']) : array();
    $to_token = isset($params['to_id']) ? $params['to_id'] : null;
    
    if(!is_null($to_token)&&!in_array($to_token,$to_tokens)&&$to_token) {
        $to_tokens[] = $to_token;
    }
    if(is_null($from_token)||!$to_tokens||!is_array($to_tokens)) {
        throw new InvalidArgumentException();
    }
    $from = $storage->User->getDataFromToken($from_token);
    $fromId = (int)$from['id'];
    $toIds = array();
    foreach($to_tokens as $to_token) {
        $to = $storage->User->getDataFromToken($to_token);
        $toId = (int)$to['id'];
        //申請状態が存在するか
        $request = $storage->UserRequestFrom->get($fromId,$toId);
        if(!$request || (int)$request['state'] !== \library\Model_UserRequestFrom::STATE_PENDING) {
            throw new ErrorException();
        }
        $toIds[$toId] = $request;
    }
    
    $storage->beginTransaction();
    foreach($toIds as $toId => $request) {
        //キャンセル
        $storage->UserRequest->updateRequest($fromId,$toId,\library\Model_UserRequest::STATE_CANCELL);
    }
    $storage->commit();
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}

