<?php
/**
 * オプション情報取得
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id'   => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
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
    if(is_null($token)||!$token) {
        throw new InvalidArgumentException();
    }

    //閲覧先
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    //オプション情報
    $option = $storage->UserOption->getOption($id);
    $option['id'] = $token;
    return \library\Response::json($option);
} catch(Exception $e) {
    return \library\Response::error($e);
}
