<?php
/**
 * メンバー招待のキャンセル
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'group_id' => 20,
        'member_ids' => array(
            'JFs0FbKckG5tyiI5bqR3L8aY0JESp9',
        ),
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
        //招待ユーザとのフレンド関係かチェック
        if(!$storage->UserFriend->checkFriend($id,(int)$mem['id'])) {
            throw new InvalidArgumentException();
        }
        $memberIds[$memberId] = $memberId;
    }
    if(!$memberIds) {
        throw new InvalidArgumentException();
    }
    
    //そのユーザは招待中か
    $list = $storage->GroupUser->checkGroupAtUserIds($groupId,$memberIds,false);
    foreach($list as $userId => $status) {
        if($status!==\library\Model_UserGroup::STATE_INVITATE) {
            unset($memberIds[$memberId]);
        }
    }
    
    if(!$memberIds) {
        throw new InvalidArgumentException();
    }
    
    //キャンセル処理
    $storage->beginTransaction();
    foreach($memberIds as $memberId) {
        $storage->Group->changeUserState($groupId,$memberId,\library\Model_UserGroup::STATE_CANCEL);
    }
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}