<?php
/**
 * 写真データの削除を行う
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'no' => 2,
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
    error_log('userPhotoDelete Error:[user_id] is not found');
    http_response_code(400);
    exit;
}
try {
    $userId = $storage->UserToken->getIdFromToken($token);
    if(!$userId) {
        //存在しないユーザ
        error_log('userPhotoDelete Error：this user is not found > '.$token);
        http_response_code(400);
        exit;
    }
    
    $user = $storage->User->primaryOne($userId);
    if(!$user) {
        //存在しないユーザ
        error_log('userPhotoDelete Error：this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
if(!isset($params['no'])) {
    error_log('userPhotoDelete Error:[no] is not found');
    http_response_code(400);
    exit;
}

$id = (int)$user['id'];

//クエリ発行
$storage->beginTransaction();
try {
    //UserPhotoの削除
    $result = $storage->UserPhoto->delete($id,(int)$params['no']);
} catch(\PdoException $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
} catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
if(!$result) {
    error_log('userPhotoDelete Error：delete failed > '.$id.'-'.$params['no']);
    http_response_code(400);
    exit;
}
$storage->commit();
return http_response_code(200);


