<?php
/**
 * 招待出来るフレンドの一覧
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'group_id' => 20,
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
    $time = isset($params['time']) ? $params['time'] : 0;
    if(is_null($token)||is_null($groupId)||!$token||!$groupId||$time<0) {
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
    //フレンドのID一覧取得
    $friendIds = $storage->UserFriend->getFriendsIds($id);
    $users = $storage->User->primary($friendIds);
    $friendIds_ = array();
    foreach($users as $user) {
        if($user['state'] == \library\Model_User::STATE_INVALID) {
            continue;
        }
        $friendIds_[] = (int)$user['id'];
    }
    $friendIds = $friendIds_;
    //グループへの所属状態を取得
    $list = $storage->GroupUser->checkGroupAtUserIds($groupId,$friendIds,false);
    $ids = array();
    foreach($friendIds as $friendId) {
        if(isset($list[$friendId]) && in_array($list[$friendId],array(\library\Model_UserGroup::STATE_INVITATE,\library\Model_UserGroup::STATE_SUBMIT))) {
            continue;
        }
        $ids[] = $friendId;
    }
    
    //メンバ取得
    $friends = $storage->User->primaryApi($ids);
    usort($friends,'friendsort');
    $ret = array(
        'user_id' => $token,
        'group_id' => $groupId,
        'count' => count($friends),
        'friend' => $friends,
    );
    return \library\Response::json($ret);
} catch (Exception $e) {
    return \library\Response::error($e);
}