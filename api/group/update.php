<?php
/**
 * グループ情報を更新する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'group_id' => 10,
        'name' => 'テストグループ+',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);

    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $groupId = isset($params['group_id']) ? (int)$params['group_id'] : null;
    $name = isset($params['name']) ? $params['name'] : null;
    $image = isset($params['image']) ? $params['image'] : null;
    if(is_null($token)||!$token||is_null($groupId)||!$groupId) {
        throw new InvalidArgumentException();
    }
    
    if(is_null($name)&&is_null($image)) {
        //どちらかは必要
        throw new InvalidArgumentException();
    }
    
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    //グループ取得
    $group = $storage->Group->getGroupOrFail($groupId);
    //グループメンバーか？
    if(!$storage->Group->checkUser($groupId,$id)) {
        throw new ErrorException();
    }
    
    //更新
    $storage->beginTransaction();
    $values = array();
    if(!is_null($name)) {
        $values['name'] = $name;
    }
    if(!is_null($image)) {
        $values['image'] = $image;
    }
    $values['update_time'] = time();
    $storage->Group->updatePrimaryOne($values, $groupId);
    
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}