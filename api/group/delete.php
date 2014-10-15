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
    if(is_null($token)||!$token||is_null($groupId)||!$groupId) {
        throw new \library\NotParamsException();
    }
    
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    $group = $storage->Group->getGroupOrFail($groupId);
    //グループの作成者か
    if($id !== (int)$group['create_user_id']) {
        throw new ErrorException();
    }
    //更新
    $storage->beginTransaction();
    $storage->Group->delete($groupId);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}