<?php
/**
 * 違反報告
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'violation_id' => 'EgdbaamiMtkr85SvlorzyvsvDuk00m',
        'message' => 'ihanihanihan',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
//json受け取り
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);
//存在チェック
$token = isset($params['user_id']) ? $params['user_id'] : null;
$vtoken = isset($params['violation_id']) ? $params['violation_id'] : null;
$message = isset($params['message']) ? $params['message'] : null;

if(is_null($token)||is_null($vtoken)) {
    error_log('messagePost Error:[user_id] or [report_id] is not found');
    http_response_code(400);
    exit;
}
if(is_null($message)) {
    error_log('messagePost Error:[message] not found');
    http_response_code(400);
    exit;
}

$user = $storage->User->getDataFromToken($token);
$violation = $storage->User->getDataFromToken($vtoken);
if(!$user) {
    //存在しないユーザ
    error_log('messagePost Error：this user is not found > '.$token);
    http_response_code(400);
    exit;
}
if(!$violation) {
    //存在しないユーザ
    error_log('messagePost Error：this user is not found > '.$vtoken);
    http_response_code(400);
    exit;
}

$userId = (int)$user['id'];
$violationId = (int)$violation['id'];

//メッセージを送信する
$storage->beginTransaction();
try{
    $values = array(
        'user_id' => $violationId,
        'reporter_id' => $userId,
        'message' => $message,
        'create_time' => time(),
    );
    $ret = $storage->Report->insertOne($values);
} catch (Exception $e) {
    error_log($e->getMessage());
    $storage->rollback();
    http_response_code(400);
    exit;
}
if(!$ret) {
    error_log('messagePost Error：insert failed > '.$id.'&'.$reporterId);
    $storage->rollback();
    http_response_code(400);
    exit;
}
$storage->commit();
http_response_code(200);