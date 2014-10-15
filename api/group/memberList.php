<?php
/**
 * グループのメンバー一覧を取得
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'group_id' => 19,
        'type' => 'all',
        'time' => 0,
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
    $type = isset($params['type']) ? $params['type'] : 'all';
    $time = isset($params['time']) ? $params['time'] : 0;
    if(is_null($token)||is_null($groupId)||!$token||!$groupId||$time<0||!$type) {
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
    $ret = array(
        'user_id' => $token,
        'group_id' => $groupId,
    );
    if($type === 'all' || $type === 'member') {
        //メンバ取得
        list($memCnt,$members) = $storage->GroupUser->getMemberList($groupId,$time);
        //ソート
        usort($members,'groupUserSort');
        $ret['member'] = array(
            'count' => $memCnt,
            'list' => $members,
        );
    }
    if($type === 'all' || $type === 'invitation') {
        //招待者取得
        list($invCnt,$invitations) = $storage->GroupUser->getInvitationList($groupId,$time);
        //ソート
        usort($invitations,'groupUserSort');
        $ret['invitation'] = array(
            'count' => $invCnt,
            'list' => $invitations,
        );
    }
    return \library\Response::json($ret);
} catch (Exception $e) {
    return \library\Response::error($e);
}