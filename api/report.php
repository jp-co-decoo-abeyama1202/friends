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
try {
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $vtoken = isset($params['violation_id']) ? $params['violation_id'] : null;
    $message = isset($params['message']) ? $params['message'] : null;
    if(is_null($token)||is_null($vtoken)||is_null($message)||!$message) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromToken($token);
    $violation = $storage->User->getDataFromToken($vtoken);
    $userId = (int)$user['id'];
    $violationId = (int)$violation['id'];

    //メッセージを送信する
    $storage->beginTransaction();
    $values = array(
        'user_id' => $violationId,
        'reporter_id' => $userId,
        'message' => $message,
        'create_time' => time(),
    );
    $storage->Report->insertOne($values);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}