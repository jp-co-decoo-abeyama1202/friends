<?php
/**
 * フレンド一覧を取得する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);

    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    if(is_null($token)) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    //フレンドのID一覧取得
    list($friends,$friendCount) = $storage->UserFriend->getFriends($id);
    foreach($friends as $friendId => $f) {
        $friends[$friendId]['friend_id'] = $friends[$friendId]['friend_token'];
        //要らないデータは削除
        unset($friends[$friendId]['friends_token']);
    }
    usort($friends,'friendsort');
    //申請している
    list($fromList,$fromCount) = $storage->UserRequestFrom->getList($id);
    //申請されている
    list($toList,$toCount) = $storage->UserRequestTo->getList($id);
    //グループ取得
    list($groups,$groupCount) = $storage->UserGroup->getGroups($id,$offset,$count);
    usort($groups,'groupsort');
    $list = array(
        'user_id' => $token,
        'friend' => array(
            'count' => $friendCount,
            'list' => $friends,
        ),
        'from' => array(
            'count' => $fromCount,
            'list' => $fromList,
        ),
        'to' => array(
            'count' => $toCount,
            'list' => $toList,
        ),
        'group' => array(
            'count' => $groupCount,
            'list' => $groups,
        ),
        'send_date' => time(),
    );
    return \library\Response::json($list);
} catch(Exception $e) {
    return \library\Response::error($e);
}

