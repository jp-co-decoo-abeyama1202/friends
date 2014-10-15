<?php
/**
 * ユーザのグループ情報を更新する
 * PUSHのON/OFFぐらいしかない。
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'group_id' => 20,
        'push_chat' => 0,
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $userToken = isset($params['user_id']) ? $params['user_id'] : null;
    $groupId = isset($params['group_id']) ? (int)$params['group_id'] : 0;
    if(is_null($userToken)||!$userToken||!$groupId) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromToken($userToken);
    $userId = (int)$user['id'];
    //グループ取得
    $group = $storage->Group->getGroupOrFail($groupId);
    //グループメンバーか？
    if(!$storage->Group->checkUser($groupId,$userId)) {
        throw new ErrorException();
    }
    $id = array(
        'user_id' => $userId,
        'group_id' => $groupId,
    );
    $storage->beginTransaction();
    //UserGroupの更新
    $keys = array('push_chat');
    $values = getKeyValues($keys,$params);
    if($values) {
        $values['update_time'] = time();
        $storage->UserGroup->updatePrimaryOne($values,$id);
    }
    $storage->commit();
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}



