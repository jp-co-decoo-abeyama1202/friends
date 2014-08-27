<?php
/**
 * フレンド一覧を取得する
 * Created by PhpStorm.
 * User: ishii
 * Date: 14/07/25
 * Time: 19:12
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'EgdbaamiMtkr85SvlorzyvsvDuk00m',
        'type' => 'from',
        'offset' => 0,
        'count' => 30,
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
    error_log('userUpdateError:必要なパラメータ[user_id]が足りません');
    http_response_code(400);
    exit;
}
try {
    $user = $storage->User->getDataFromToken($token);
    if(!$user) {
        //存在しないユーザ
        error_log('friendRequestList Error：this user is not found > '.$token);
        http_response_code(400);
        exit;
    }
} catch (PDOException $ex) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
$id = (int)$user['id'];
$type = isset($params['type']) ? $params['type'] : 'from';
$offset = isset($params['offset']) ? (int)$params['offset'] : 0;
$count = isset($params['count']) ? (int)$params['count'] : \library\Model_UserRequestFrom::DEFAULT_COUNT;
try{
    if($type === 'to') {
        $list = $storage->UserRequestTo->getList($id);
    } else {
        $list = $storage->UserRequestFrom->getList($id);
    }
}catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
$ret = array(
    'user_id' => $token,
    'type' => $type,
    'offset' => $offset,
    'count' => count($list),
    'list' => $list,
);
echo json_encode($ret);
return http_response_code(200);
