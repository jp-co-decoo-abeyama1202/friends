<?php
/**
 * プロフィール画像を登録する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'image' => 'test1111',
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
    //クエリ発行
    $storage->beginTransaction();
    $values = array(
        'image' => $params['image'],
        'update_time' => time(),
    );
    //Userの更新
    $result = $storage->User->updatePrimaryOne($values,$id);
    $storage->commit();
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}



