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
        'from_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'to_id'   => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
        'text' => 'aaaaaaaaaa'
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
    error_log('userGetError:[token] not found');
    http_response_code(400);
    exit;
}
try {
    $from = $storage->User->getDataFromToken($from_token);
    if(!$from) {
        //存在しないユーザ
        error_log('friendRequest Error：this user is not found > '.$from_token);
        http_response_code(400);
        exit;
    }
    $to = $storage->User->getDataFromToken($to_token);
    if(!$to) {
        //存在しないユーザ
        error_log('friendRequest Error：this user is not found > '.$to_token);
        http_response_code(400);
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}

$fromId = (int)$from['id'];
$toId   = (int)$to['id'];
$text   = isset($params['text']) ? $params['text'] : '';
$state  = \library\Model_UserRequestFrom::STATE_PENDING;

//ブロック状態確認
if($storage->UserBlock->check($fromId,$toId,true)) {
    //どちらかがブロックしている
    error_log('friendRequest Error: > blocked now '.$from_token);
    http_response_code(400);
    exit;
}

$storage->beginTransaction();
try {
    $requestF = $storage->UserRequestFrom->get($fromId,$toId);
    if($requestF) {
        if((int)$requestF['state'] === \library\Model_UserRequestFrom::STATE_REFUSE
            || (int)$requestF['state'] === \library\Model_UserRequestFrom::STATE_EXECUTE) {
            //拒否されてる
            error_log('friendRequest Error:request state is '.$requestF['state'].' > '.$fromId . '&' . $toId);
            http_response_code(400);
            $storage->rollBack();
            exit;
        }
        //message更新
        $messageId = (int)$requestF['message_id'];
        $storage->UserRequestMessage->update($messageId,$fromId,$text);
    } else {
        //message登録
        $messageId = $storage->UserRequestMessage->add($fromId,$text);
    }
    if(!$messageId) {
        error_log('friendRequest Error:message_id retrieving failed > '.$fromId . '&' . $toId);
        http_response_code(400);
        $storage->rollBack();
        exit;
    }
    //UserRequestFrom登録
    $resultF = $storage->UserRequestFrom->add($fromId,$toId,$messageId,$state);
    //UserRequestTo登録
    $resultT = $storage->UserRequestTo->add($toId,$fromId,$messageId,$state);
    
    //TO側にPUSH通知
    $option = $storage->UserOption->primaryOne($toId);
    if($option && $option['push_friend'] == \library\Model_UserOption::FLAG_ON && $to['push_id']) {
        //PUSH送信
        $device = $to['device'] == \library\Model_User::DEVICE_IOS ? \library\Push::TYPE_IOS : \library\Push::TYPE_ANDROID;
        $bool = $push->send(
            $device,
            $to['push_id'],
            $push->message($from['name'].'さんからフレンド申請が届きました')
            ->badge(3)
            ->sound(null)
        );
        $values = array(
            'from_id' => $fromId,
            'to_id' => $toId,
            'type' => \library\Model_Push::TYPE_REQUEST,
            'result' => $bool ? \library\Model_Push::RESULT_SUCCESS : \library\Model_Push::RESULT_FAILED,
            'create_time' => time()
        );
        $storage->Push->insertOne($values);
    }
}catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    $storage->rollback();
    exit;
}

if(!$resultF||!$resultT) {
    error_log('friendRequest Error:insert failed > '.$fromId . '&' . $toId);
    http_response_code(400);
    $storage->rollBack();
    exit;
}
$storage->commit();
return http_response_code(200);

