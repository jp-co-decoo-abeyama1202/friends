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
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);

    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    if(is_null($token)||!isset($params['no'])) {
        error_log('userPhotoDelete Error:[user_id] is not found');
        http_response_code(400);
        exit;
    }
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    //クエリ発行
    $storage->beginTransaction();
    //UserPhotoの削除
    $result = $storage->UserPhoto->delete($id,(int)$params['no']);
    $storage->commit();
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}



