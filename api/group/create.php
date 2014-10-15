<?php
/**
 * グループを作成する。
 * 名前(name)は必須。
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'name' => 'テストグループ',
        'image' => 'image',
        'member_ids' => ''
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);

    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $name = isset($params['name']) ? $params['name'] : null;
    $image = isset($params['image']) ? $params['image'] : '';
    $memberTokens = isset($params['member_ids']) ? explode(",",$params['member_ids']) : array();
    if(is_null($token)||is_null($name)||!$token||!$name||!is_array($memberTokens)) {
        throw new \library\NotParamsException();
    }
    
    $user = $storage->User->getDataFromToken($token);
    $id = (int)$user['id'];
    $memberIds = array();
    foreach($memberTokens as $memberToken) {
        $mem = $storage->User->getDataFromToken($memberToken);
        $memberIds[] = (int)$mem['id'];
    }
    if(!in_array($id,$memberIds)) {
        $memberIds[] = $id;
    }
    if(!$storage->Group->checkUserLastCreateTime($id)) {
        //1分間に1個までしか作れない
        return \library\Response::error();
    }
    
    //登録
    $storage->beginTransaction();
    $storage->Group->create($id,$name,$image,$memberIds);
    $storage->commit();
    return \library\Response::success();
} catch (Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}