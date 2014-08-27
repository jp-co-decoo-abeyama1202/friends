<?php
/**
 * 各ファイルから読み込まれるファイル
 * 基本設定等は全部ココが基準
 */
//定数読み込み
require_once('/home/homepage/html/public/friends/inc/const.php');

if($_SERVER["SERVER_ADDR"]===ADDR_TEST){
    //テストサーバ
    define('DIR_CONFIG','/home/homepage/html/public/friends/config/test');
}else{
    define('DIR_CONFIG','/home/homepage/html/public/friends/config');
}

//別ファイルの読み込み
require_once(DIR_LIBRARY . '/Autoloader.php');
$autoloader = Autoloader::getInstance();
if (!$autoloader->isEnabled()) {
    $autoloader->addDirectory(DIR_LIBRARY,'library');
    $autoloader->enable();
}
require_once(DIR_CONFIG  . '/config.php');
$config_data = array(
    'db_config'    => $_db_config,
    'table_config' => $_table_config,
    'push_config'  => $_push_config,
    'emoji_config' => array(
        'image_url' => EMOJI_DIR,
        'instances' => array('iphone')
    ),
);

$config = new \library\Config($config_data);
$storage = new \library\Storage($config);
$push = new \library\Push($_push_config);

/*
$now = time();
if(time() === $now) {
    echo json_encode(array(
            'text' => 'メンテナンス中です',
        )
    );
    return http_response_code(503);
}
*/
require_once(__DIR__ . '/function.php');
