<?php
/**
 * フレンド一覧を取得する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
//受信jsonパラメータ
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);

//存在チェック
$token = isset($params['user_id']) ? $params['user_id'] : null;
if(is_null($token)) {
    error_log('friendGet Error:[user_id] is not found');
    http_response_code(400);
    exit;
}
try {
    $user = $storage->User->getDataFromToken($token);
    if(!$user) {
        //存在しないユーザ
        error_log('friendGet Error：this user is not found > '.$token);
        http_response_code(400);
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
$id = (int)$user['id'];
try{
    //フレンドのID一覧取得
    $friends = $storage->UserFriend->getFriends($id);
    $users = $storage->User->getDataFromRequestList(array_keys($friends));
    foreach($friends as $friendId => $f) {
        $friends[$friendId] += $users[$friendId];
        $friends[$friendId]['friend_id'] = $friends[$friendId]['id'];
        //要らないデータは削除
        unset($friends[$friendId]['id']);
        unset($friends[$friendId]['user_id']);
        unset($friends[$friendId]['friends_id']);
    }
    usort($friends,'mysort');
}catch(PDOException $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
$list = array(
    'user_id' => $token,
    'count' => count($friends),
    'list' => $friends,
);
echo json_encode($list);
http_response_code(200);

/**
 * ソート用関数
 * 最近ログインしたユーザを上
 * ログイン時間が同じなら、
 * 最近フレンドになったユーザが上
 * @param type $a
 * @param type $b
 * @return int
 */
function mysort($a,$b) {
    if($a['login_time'] === $b['login_time']) {
        if($a['create_time'] === $b['create_time']) {
            return 0;
        }
        return $a['create_time'] > $b['create_time'] ? -1 : 1;
    }
    return $a['login_time'] > $b['login_time'] ? -1 : 1;
}