<?php
/**
 * アプリの最新バージョンを返す
 * 最新バージョンの管理は friends/inc/define.php
 * Created by PhpStorm.
 * User: ishii
 * Date: 14/07/28
 * Time: 18:34
 */
require('../inc/define.php');
$appVersion = array('version'=>NOW_VERSION);

$json = json_encode($appVersion);
echo $json;
return http_response_code(200);