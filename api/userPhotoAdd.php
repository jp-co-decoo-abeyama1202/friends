<?php
/**
 * 写真データを登録する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'image' => 'test2_2_2',
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
    error_log('userPhotoAdd Error:[user_id] is not found');
    http_response_code(400);
    exit;
}
try {
    $userId = $storage->UserToken->getIdFromToken($token);
    if(!$userId) {
        //存在しないユーザ
        error_log('userPhotoAdd Error：this user is not found > '.$token);
        http_response_code(400);
        exit;
    }
    
    $user = $storage->User->primaryOne($userId);
    if(!$user) {
        //存在しないユーザ
        error_log('userPhotoAdd Error：this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
if(!isset($params['image'])) {
    error_log('userPhotoAdd Error:[image] is not found');
    http_response_code(400);
    exit;
}
$id = (int)$user['id'];

//登録可能かチェック
if($storage->UserPhoto->getUserPhotoCount($id) >= \library\Model_UserPhoto::MAX_COUNT) {
    error_log('userPhotoAdd Error:max_count over > '.$id);
    http_response_code(400);
    exit;
}

//クエリ発行
$storage->beginTransaction();
try {
    //UserPhotoの登録
    $result = $storage->UserPhoto->add($id,$params['image']);
    
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
    error_log('userPhotoAdd Error：insert failed > '.$udid);
    http_response_code(400);
    exit;
}
$storage->commit();
return http_response_code(200);


