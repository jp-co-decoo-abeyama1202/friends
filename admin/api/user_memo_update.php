<?php
require_once(__DIR__."/_header.php");
//受信jsonパラメータ
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);

//存在チェック
$userId = isset($params['user_id']) ? $params['user_id'] : null;
$memoData = isset($params['memo']) ? $params['memo'] : null;
if(is_null($userId)) {
    error_log('user_memo_update error:[user_id] is not found');
    http_response_code(400);
    exit;
}
if(is_null($memoData)) {
    error_log('user_memo_update error:[$memoData] is not found');
    http_response_code(400);
    exit;
}
try {
    $user = $storage->User->primaryOne($userId);
    if(!$user) {
        //存在しないユーザ
        error_log('user_memo_update error：this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (Exception $ex) {
    error_log($ex->getMessage());
    http_response_code(400);
    exit;
}

//クエリ発行
$storage->beginTransaction();
try {
    $values = array(
        'id' => $userId,
        'memo' => $memoData,
        'update_time' => time(),
    );
    //UserMemoの更新
    $result = $storage->UserMemo->insertOne($values);
    
} catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
if(!$result) {
    error_log('user_memo_update error：update failed > '.$userId . '[' .$memoData . ']');
    http_response_code(400);
    exit;
}
$storage->commit();
return http_response_code(200);
