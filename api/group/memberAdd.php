<?php
/**
 * グループにメンバーを招待する
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
        //招待ユーザとのフレンド関係かチェック
        if(!$storage->UserFriend->checkFriend($id,(int)$mem['id'])) {
            throw new InvalidArgumentException();
        }
        $memberIds[$memberId] = $memberId;
    }
    if(!$memberIds) {
        throw new InvalidArgumentException();
    }
    
    //招待ユーザが既に申請済みや参加済みでないか
    $list = $storage->GroupUser->checkGroupAtUserIds($groupId,$memberIds,false);
    foreach($list as $userId => $status) {
        if(in_array($status,array(\library\Model_UserGroup::STATE_SUBMIT,\library\Model_UserGroup::STATE_INVITATE))) {
            unset($memberIds[$memberId]);
        }
    }
    
    if(!$memberIds) {
        throw new InvalidArgumentException();
    }
    
    //現在のメンバー数と上限のチェック
    $memberCount = $storage->GroupUser->getMemberCount($groupId);
    if($memberCount + count($memberIds) > \library\Model_GroupUser::MAX_USER) {
        throw new OutOfBoundsException();
    }
    
    //登録
    $storage->beginTransaction();
    
    $storage->Group->addUsers($groupId,$memberIds,$id);
    foreach($memberIds as $memberId) {
        $storage->PushQueue->add($id,$memberId,\library\Model_Push::TYPE_GROUP_INVITE);
    }
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}