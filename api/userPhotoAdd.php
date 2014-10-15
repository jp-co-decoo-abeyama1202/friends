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
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);

    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    if(is_null($token)||!isset($params['image'])) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];

    //登録可能かチェック
    if($storage->UserPhoto->getUserPhotoCount($id) >= \library\Model_UserPhoto::MAX_COUNT) {
        throw new BadMethodCallException();
    }
    //クエリ発行
    $storage->beginTransaction();
    //UserPhotoの登録
    $result = $storage->UserPhoto->add($id,$params['image']);
    $storage->commit();
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}



