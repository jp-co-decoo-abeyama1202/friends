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
try {
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    if(is_null($token)) {
        throw new InvalidArgumentException();
    }

    $userId = $storage->UserToken->getIdFromToken($token);
    $user = $storage->User->primaryOne($userId);
    $id = (int)$userId;
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
    //ログイン時間更新
    if($user['login_time'] + 60 < time()) {
        $storage->beginTransaction();
        $result = $storage->User->updateLogintime($id);
        $storage->commit();
    }
    return \library\Response::json($list);
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}