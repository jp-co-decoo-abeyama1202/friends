<?php
/**
 * グループ一覧を取得する
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
        throw new \library\NotParamsException();
    }
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
    $count = isset($params['count']) ? (int)$params['count'] : \library\Model_UserGroup::DEFAULT_COUNT;
    //グループ取得
    list($groups,$groupCount) = $storage->UserGroup->getGroups($id,$offset,$count);
    usort($groups,'groupsort');
    $list = array(
        'user_id' => $token,
        'count' => $groupCount,
        'list' => $groups,
        'option' => array(
            'offset' => $offset,
            'count' => $count,
        ),
    );
    return \library\Response::json($list);
} catch (Exception $e) {
    return \library\Response::error($e);
}
