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
        'to_id'   => 'rIzsn5DOZlVeoBrEXXNoPLLnlac8mJ',
        'state'  => 2,
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
    error_log('friendRequestProcess Error:[from_id] or [to_id] is not found');
    http_response_code(400);
    exit;
}
try {
    $from = $storage->User->getDataFromToken($from_token);
    if(!$from) {
        //存在しないユーザ
        error_log('friendRequestProcess Error：this user is not found > '.$from_token);
        http_response_code(400);
        exit;
    }
    $to = $storage->User->getDataFromToken($to_token);
    if(!$to) {
        //存在しないユーザ
        error_log('friendRequestProcess Error：this user is not found > '.$to_token);
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
$state  = isset($params['state']) ? (int)$params['state'] : null;
if(is_null($state)) {
    error_log('friendRequestProcess Error:[state] is not found');
    http_response_code(400);
    exit;
}

//まず申請状態が存在するか
$request = $storage->UserRequestFrom->get($fromId,$toId);
if(!$request || (int)$request['state'] !== \library\Model_UserRequestFrom::STATE_PENDING) {
    error_log('friendRequestProcess Error:state is not pending > ' .$fromId."->".$toId);
    http_response_code(400);
    exit;
}

$storage->beginTransaction();
try {
    //UserRequestFrom更新
    $resultF = $storage->UserRequestFrom->update($fromId,$toId,$state);
    //UserRequestTo更新
    $resultT = $storage->UserRequestTo->update($toId,$fromId,$state);
    if($state === \library\Model_UserRequestFrom::STATE_EXECUTE) {
        //フレンドに登録
        $friendsId = $storage->Friends->add($fromId,$toId);
        if(!$friendsId) {
            error_log('friendRequestProcess Error:friends insert failed > '.$fromId."->".$toId);
            http_response_code(400);
            $storage->rollBack();
            exit;
        }
        $result = $storage->UserFriend->add($fromId,$toId,$friendsId);
        
        //PUSHを飛ばそう
        //ここはfrom側に飛ばすので注意
        $option = $storage->UserOption->primaryOne($fromId);
        if($option && $option['push_friend'] == \library\Model_UserOption::FLAG_ON && $from['push_id']) {
            //PUSH送信
            $device = $from['device'] == \library\Model_User::DEVICE_IOS ? \library\Push::TYPE_IOS : \library\Push::TYPE_ANDROID;
            $bool = $push->send(
                $device,
                $from['push_id'],
                $push->message($to['name'].'さんとフレンドになりました！')
                ->badge(3)
                ->sound(null)
            );
            $values = array(
                'from_id' => $toId,
                'to_id' => $fromId,
                'type' => \library\Model_Push::TYPE_FRIEND,
                'result' => $bool ? \library\Model_Push::RESULT_SUCCESS : \library\Model_Push::RESULT_FAILED,
                'create_time' => time()
            );
            $storage->Push->insertOne($values);
        }
    }
}catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    $storage->rollback();
    exit;
}

if(!$resultF||!$resultT||!$result) {
    error_log('friendRequest Error:insert failed > '.$fromId."->".$toId);
    http_response_code(400);
    $storage->rollback();
    exit;
}
$storage->commit();
return http_response_code(200);
