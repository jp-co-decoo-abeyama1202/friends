<?php
/**
 * メンバーからの承認・拒否
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
        'group_id' => 20,
        'state' => 1,
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);

    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $groupId = isset($params['group_id']) ? $params['group_id'] : null;
    $state = isset($params['state']) ? $params['state'] : null;
    if(is_null($token)||is_null($groupId)||!$token||!$groupId||is_null($state)||!in_array($state,array(\library\Model_UserGroup::STATE_SUBMIT,\library\Model_UserGroup::STATE_REFUSE))) {
        throw new InvalidArgumentException();
    }
    
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
     //グループ取得
    $group = $storage->Group->getGroupOrFail($groupId);
    
    $userGroup = $storage->UserGroup->get($id,$groupId);
    
    if((int)$userGroup['state'] !== \library\Model_UserGroup::STATE_INVITATE) {
        throw new InvalidArgumentException();
    }
    //ステータス更新
    $storage->beginTransaction();
    $storage->Group->changeUserState($groupId,$id,$state);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}