<?php
/**
 * 友達効を取得
 * Created by PhpStorm.
 * User: ishii
 * Date: 14/07/25
 * Time: 19:31
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'offset' => 0,
    );
    $_POST['params'] = json_encode($test_params);
}
*/
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);

//存在チェック
$token = isset($params['user_id']) ? $params['user_id'] : null;
if(is_null($token)) {
    error_log('search Error:[user_id] is no found');
    http_response_code(400);
    exit;
}
try {
    $userId = $storage->UserToken->getIdFromToken($token);
    if(!$userId) {
        //存在しないユーザ
        error_log('search Error：this user is not found > '.$token);
        http_response_code(400);
        exit;
    }
    $user = $storage->User->primaryOne($userId);
    if(!$user) {
        //存在しないユーザ
        error_log('search Error：this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
$id = (int)$user['id'];
$offset = isset($params['offset']) ? (int)$params['offset'] : 0;
$count = isset($params['count']) ? (int)$params['count'] : \library\Model_User::SEARCH_DEFAULT;
//一覧取得
$rows = $storage->User->search($id,$offset,$count);
http_response_code(200);
$list = array(
    'user_id' => $token,
    'offset' => $offset,
    'count' => count($rows),
    'list' => $rows,
);
echo json_encode($list);
exit;