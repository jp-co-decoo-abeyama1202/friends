<?php
/**
 * ユーザ情報を更新する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'login_time' => 1,
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
    error_log('userUpdateError:[user_id] is not found');
    http_response_code(400);
    exit;
}
try {
    $userId = $storage->UserToken->getIdFromToken($token);
    
    if(!$userId) {
        //存在しないユーザ
        error_log('userUpdate Error：this user is not found > '.$token);
        http_response_code(400);
        exit;
    }
    
    $user = $storage->User->primaryOne($userId);
    
    if(!$user) {
        //存在しないユーザ
        error_log('userUpdate Error：this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (PDOException $ex) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}

//クエリ発行
$id = (int)$user['id'];
$uuAdd = false;
$storage->beginTransaction();
try {
    $result1 = $result2 = $result3 = true;
    $now = time();
    //Userの更新
    $keys = array('name','sex','age','country','area','request','publishing','profile','device','state','login_time','image');
    $values = getKeyValues($keys,$params);
    if($values) {
        if(isset($values['login_time'])) {
            $values['login_time'] = $now;
            $uuAdd = true;
        }
        $values['update_time'] = $now;
        $result1 = $storage->User->updatePrimaryOne($values,$id);
    }
    //UserCommentの登録・更新
    $keys = array('id','title','text');
    $values = getKeyValues($keys,$params,'comment_');
    if($values && count($values) === 3) { //コメントの更新は3つ全て揃ってないと行わない
        $values['comment_id'] = $values['id'];
        unset($values['id']);
        $values['user_id'] = $id;
        $values['create_time'] = $now;
        $values['update_time'] = $now;
        $result2 = $storage->UserComment->insertOne($values);
    }
    //UserOptionの更新
    $keys = array('sex','min_age','max_age','country','area','push_friend','push_chat');
    $values = getKeyValues($keys,$params,'option_');
    if($values) {
        $values['update_time'] = $now;
        $result3 = $storage->UserOption->updatePrimaryOne($values,$id);
    }
    //UUの集計
    if($uuAdd) {
        $storage->UuDaily->add($id);
    }
} catch(\PdoException $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
if(!$result1 || !$result2 || !$result3) {
    error_log('userUpdateError：update failed > '.$userId);
    http_response_code(400);
    exit;
}
$storage->commit();
return http_response_code(200);


