<?php
/**
 * ユーザ情報を更新する
 * Created by PhpStorm.
 * User: ishii
 * Date: 14/07/28
 * Time: 17:22
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
//受信jsonパラメータ
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);

//存在チェック
$token = isset($params['user_id']) ? $params['user_id'] : null;
if(is_null($token)) {
    error_log('userImageAdd Error:[$token] is not found');
    http_response_code(400);
    exit;
}
try {
    $userId = $storage->UserToken->getIdFromToken($token);
    if(!$userId) {
        //存在しないユーザ
        error_log('userImageDelete Error：this user is not found > '.$token);
        http_response_code(400);
        exit;
    }
    $user = $storage->User->primaryOne($userId);
    if(!$user) {
        //存在しないユーザ
        error_log('userImageDelete Error：this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (Exception $ex) {
    error_log($ex->getMessage());
    http_response_code(400);
    exit;
}

$id = (int)$user['id'];
//クエリ発行
$storage->beginTransaction();
try {
    $values = array(
        'image' => null,
        'update_time' => time(),
    );
    //Userの更新
    $result = $storage->User->updatePrimaryOne($values,$id);
} catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
if(!$result) {
    error_log('userImageDelete Error：update failed > '.$id);
    http_response_code(400);
    exit;
}
$storage->commit();
return http_response_code(200);


