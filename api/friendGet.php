<?php
/**
 * フレンド一覧を取得する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'offset' => 0,
        'count' => 30,
    );
    $_POST['params'] = json_encode($test_params);
}
*/
//存在チェック
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    if(is_null($token)) {
        throw new OutOfBoundsException();
    }
    $user = $storage->User->getDataFromToken($token);
    if(!$user) {
        throw new OutOfBoundsException();
    }
    $id = (int)$user['id'];
    $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
    $count = isset($params['count']) ? (int)$params['count'] : \library\Model_UserFriend::DEFAULT_COUNT;
    //フレンドのID一覧取得
    list($friends,$friendCount) = $storage->UserFriend->getFriends($id,$offset,$count);
    foreach($friends as $friendId => $f) {
        $friends[$friendId]['friend_id'] = $friends[$friendId]['friend_token'];
        //要らないデータは削除
        unset($friends[$friendId]['friends_token']);
    }
    usort($friends,'friendsort');
    $list = array(
        'user_id' => $token,
        'count' => $friendCount,
        'list' => $friends,
        'option' => array(
            'offset' => $offset,
            'count' => $count,
        ),
    );
    return \library\Response::json($list);
} catch (Exception $e) {
    return \library\Response::error($e);
}
