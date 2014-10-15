<?php
/**
 * ログイン処理
 */
require('../inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'app_version' => '0.1.1',
        'udid' => 'A5C26C46-11D3-42F2-8D81-2CE7529DC9B6',
        'push_id' => '4150f14a7fbe30197cd87f4edc71984df60cf6bac28c8e84cdb53ca60dde64dc',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    $appVersion = isset($params['app_version']) ? $params['app_version'] : null;
    if(is_null($appVersion)) {
        return http_response_code(400);
    }
    $compare = version_compare($appVersion,NOW_VERSION);
    $isDemand = false;
    if($compare === 1) {
        //現行バージョンより大きいので審査中扱い
        $isDemand = true;
    }
    $response = array('version'=>NOW_VERSION,'compare'=>$compare,'is_demand'=>$isDemand);
    //userの更新処理
    $udid = isset($params['udid']) ? $params['udid'] : null;
    $pushId = isset($params['push_id']) ? $params['push_id'] : '';
    if(!$udid) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromUdid($udid);
    $id = (int)$user['id'];
    $storage->beginTransaction();
    $storage->User->login($id,$pushId);
    //UUの集計
    $storage->UuDaily->add($id);
    
    $storage->commit();
    return \library\Response::json($response);
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}
