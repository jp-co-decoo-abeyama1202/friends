<?php
/**
 * グループのメンバーを削除する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'group_id' => 20,
        'member_ids' => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
    );
    $_POST['params'] = json_encode($test_params);
}
 * 
 */
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);

    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $groupId = isset($params['group_id']) ? $params['group_id'] : null;
    $memberTokens = isset($params['member_ids']) ? explode(",",$params['member_ids']) : array();
    if(is_null($token)||is_null($groupId)||!$token||!$groupId||!$memberTokens || !is_array($memberTokens)) {
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
    
    $memberIds = array();
    foreach($memberTokens as $memberToken) {
        $mem = $storage->User->getDataFromToken($memberToken);
        $memberId = (int)$mem['id'];
        $memberIds[] = $memberId;
    }
    if(!$memberIds) {
        throw new InvalidArgumentException();
    }
    //削除可能か判定
    $admin = (int)$group['create_user_id'] === $id ? true : false;
    if(!$admin && (count($memberIds)>1 || !in_array($id,$memberId))) {
        throw new InvalidArgumentException();
    }
    
    //メンバ削除
    $storage->beginTransaction();
    $storage->Group->deleteUsers($groupId,$memberIds,$id);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}