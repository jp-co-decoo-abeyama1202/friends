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
        'user_id' => 'Ck5X2BWykFpOI6qf1aC9auuWBX4eBq',
        'type' => 'to',
        'offset' => 0,
        'count' => 30,
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
        throw new \library\NotParamsException();
    }
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    $type = isset($params['type']) && $params['type'] == 'to' ? $params['type'] : 'from';
    $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
    $count = isset($params['count']) ? (int)$params['count'] : \library\Model_UserRequestFrom::DEFAULT_COUNT;
    if($type === 'to') {
        list($list,$allCount) = $storage->UserRequestTo->getList($id,$offset,$count);
    } else {
        list($list,$allCount) = $storage->UserRequestFrom->getList($id,$offset,$count);
    }
    $ret = array(
        'user_id' => $token,
        'type' => $type,
        'count' => $allCount,
        'list' => $list,
        'option' => array(
            'offset' => $offset,
            'count' => $count,
        ),
    );
    return \library\Response::json($ret);
} catch(Exception $e) {
    return \library\Response::error($e);
}
