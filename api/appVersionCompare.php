<?php
/**
 * アプリが審査中かを返す
 * 最新バージョンの管理は friends/inc/define.php
 */
require('../inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'app_version' => '0.1.1',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
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
return \library\Response::json($response);