<?php
/**
 * プロフィール情報取得
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
    //画像一覧取得
    $user['photo'] = $storage->UserPhoto->getPhotoList($id);
    //コメント一覧取得
    $user['comment'] = $storage->UserComment->getCommentList($id);
    //IDにトークンセット
    $user['id'] = $token;
    //不要データの削除
    $unsetList = array('udid','push_id');
    foreach($unsetList as $key) {
        unset($user[$key]);
    }
    return \library\Response::json($user);
} catch(Exception $e) {
    return \library\Response::error($e);
}
