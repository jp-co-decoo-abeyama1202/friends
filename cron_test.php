<?php
/**
 * テストサーバ用Cron実行ファイル
 * [実行の仕方]
 * cron.php Method [引数1] [引数2]
 * [memo]
 * argv = コマンドラインから渡された引数の詰まった配列
 * argc = コマンドラインから実行した時の引数の数
 */
//定数読み込み
require_once('/home/homepage/html/public/friends/inc/const.php');
const DIR_CONFIG = '/home/homepage/html/public/friends/config/test';

//別ファイルの読み込み
require_once(DIR_LIBRARY . '/Autoloader.php');
$autoloader = Autoloader::getInstance();
if (!$autoloader->isEnabled()) {
    $autoloader->addDirectory('/home/homepage/html/public/friends/admin/library','library\admin');
    $autoloader->addDirectory(DIR_LIBRARY,'library');
    $autoloader->enable();
}

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

if (!isset($argv[1]) || !\is_string($argv[1])) {
    exit(1);
}

$_args = array();
if ($argc > 1) {
    for ($i = 2; $i < $argc; ++$i) {
        $_args[] = $argv[$i];
    }
}
$cronName = 'Cron'.$argv[1];
$filePath = CRON_DIR . DIRECTORY_SEPARATOR . $cronName . '.php';
if(!file_exists($filePath)) {
    exit(1);
}
//実行Cronを読み込む
include_once($filePath);
$cron = new $cronName($config);
if($cron instanceof \library\Cron) {
    exit($cron->run($_args) === true ? 0 : 1);
} else {
    error_log("not cron");
    exit(1);
}