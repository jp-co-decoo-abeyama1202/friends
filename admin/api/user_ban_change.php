<?php
require_once(__DIR__."/_header.php");
//受信jsonパラメータ
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);

//存在チェック
$userId = isset($params['user_id']) ? $params['user_id'] : null;
$mode = isset($params['mode']) ? $params['mode'] : null;
if(is_null($userId)) {
    error_log('user_ban_change error:[user_id] is not found');
    error_log('params:' . $json);
    http_response_code(400);
    exit;
}
if(is_null($mode)&&!in_array($mode,array('on','off'))) {
    //理由は必須
    error_log('user_ban_change error:[$mode] is not found');
    error_log('params:' . $json);
    http_response_code(400);
    exit;
}

try {
    $user = $storage->User->primaryOne($userId);
    if(!$user) {
        //存在しないユーザ
        error_log('user_ban_change error:this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (Exception $ex) {
    error_log($ex->getMessage());
    http_response_code(400);
    exit;
}

//現在執行中のBAN情報があるか
$ban = $storage->UserBan->primaryOne($userId);
if(!$ban) {
    error_log('user_ban_change error:ban data is not found');
    http_response_code(400);
    exit;
}

//クエリ発行
$storage->beginTransaction();
try {
    $values = array(
        'available' => $mode === 'on' ? \library\Model_UserBan::AVAILABLE_TRUE : \library\Model_UserBan::AVAILABLE_FALSE,
        'update_time' => time(),
    );
    //データ更新
    $storage->UserBan->updatePrimaryOne($values,$userId);
    
} catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}

$storage->commit();
return http_response_code(200);
