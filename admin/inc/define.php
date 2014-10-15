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
    define('ADDR',ADDR_TEST);
}elseif($_SERVER["SERVER_ADDR"]===ADDR_LOCAL) {
    define('DIR_CONFIG','/home/homepage/html/public/friends/config/local');
    define('ADDR',ADDR_LOCAL);
}else{
    define('DIR_CONFIG','/home/homepage/html/public/friends/config');
    define('ADDR',ADDR_PRODUCTION);
}

//審査中かどうか
if(isset($_POST['review'])&&$_POST['review']){
    define("FLAG_REVIEW",true);
}else{
    define("FLAG_REVIEW",false);
}

//別ファイルの読み込み
require_once(DIR_LIBRARY . '/Autoloader.php');
$autoloader = Autoloader::getInstance();
if (!$autoloader->isEnabled()) {
    $autoloader->addDirectory('/home/homepage/html/public/friends/admin/library','library\admin');
    $autoloader->addDirectory(DIR_LIBRARY,'library');
    $autoloader->enable();
}

require_once(DIR_LIBRARY . '/HTML/Emoji.php');
$emoji = HTML_Emoji::getInstance();
$emoji->setImageUrl('http:///friends/assets/img/emoji/');

require_once(DIR_LIBRARY . '/Assets.php');
$url = 'http://' . ADDR . '/friends/admin/';
$assets = array(
    'root' => array(
        'path' => '/home/homepage/html/public/friends/admin/views/',
        'uri'  => $url . 'views/',
    ),
    'css'  => array(
        'path' => '/home/homepage/html/public/friends/admin/assets/css/',
        'uri'  => $url . 'assets/css/',
    ),
    'js'  => array(
        'path' => '/home/homepage/html/public/friends/admin/assets/js/',
        'uri'  => $url . 'assets/js/',
    ),
    'img'  => array(
        'path' => '/home/homepage/html/public/friends/admin/assets/img/',
        'uri'  => $url . 'assets/img/',
    ),
    'api'  => array(
        'path' => '/home/homepage/html/public/friends/admin/api/',
        'uri'  => $url . 'api/',
    ),
    'test_api'  => array(
        'path' => '/home/homepage/html/public/friends/api/',
        'uri'  => 'http://'. ADDR . '/friends/api/',
    )
);
\library\Assets::set($assets);
include_once(DIR_CONFIG  . '/config.php');
$config_data = array(
    'db_config'    => $_db_config,
    'table_config' => $_table_config,
    'push_config'  => $_push_config,
    'model_dirs'    => array(
        'library\admin' => '/home/homepage/html/public/friends/admin/library/Model',
    ),
);

$config = new \library\Config($config_data);
$storage = new \library\Storage($config);

require_once(__DIR__ . '/function.php');
