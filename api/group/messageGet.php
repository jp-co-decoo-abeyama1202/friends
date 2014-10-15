<?php
/**
 * グループのメッセージを取得
 * 退出していた場合は入っていた時までのログしか取得出来ない。
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'group_id' => 20,
        'offset' => 0,
        'count' => \library\Model_GroupMessage::DEFAULT_COUNT,
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
    $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
    $count = isset($params['count']) ? (int)$params['count'] : \library\Model_GroupMessage::DEFAULT_COUNT;
    if(is_null($token)||is_null($groupId)||!$token||!$groupId||$offset<0||$count<0) {
        throw new InvalidArgumentException();
    }
    
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    //グループ取得
    $group = $storage->Group->getGroupOrFail($groupId);
    //グループへの所属経験があるか
    if(!$storage->UserGroupLog->checkAddGroupHistory($id,$groupId)) {
        //表示されているのがおかしい
        throw new InvalidArgumentException();
    }
    //取得
    $ret = array(
        'user_id' => $token,
        'group_id' => $groupId,
        'option' => array(
            'offset' => $offset,
            'count' => $count,
        ),
        'messages' => $storage->GroupMessage->getMessage($id,$groupId,$offset,$count),
        'members' => $storage->GroupUser->getTokens($id,$groupId),
    );
    return \library\Response::json($ret);
} catch (Exception $e) {
    return \library\Response::error($e);
}