<?php
/**
 * 共通以外の定数を管理する。こちらは本番用。
 */
const IS_TEST = false;
const BASE_URL = 'http://133.242.23.29/';
const PEM_PATH = '';

/**
 * DBへの接続情報を設定する。
 * 現状対応しているのがMySqlのみ。
 * 定数や、各値についてはlibrary\Configを参照の事。
 */
$_db_config =  array(
    'default' => array(
        \library\Config::DB_CONFIG_DBNAME   => 'friend',
        \library\Config::DB_CONFIG_HOST     => 'localhost',
        \library\Config::DB_CONFIG_USERNAME => 'root',
        \library\Config::DB_CONFIG_PASSWORD => 'Vao5yBkpgkoRbEk0d9BDaMQU9bHeay1w',
    ),
    'statistics' => array(
        \library\Config::DB_CONFIG_DBNAME   => 'friends_admin',
        \library\Config::DB_CONFIG_HOST     => 'localhost',
        \library\Config::DB_CONFIG_USERNAME => 'root',
        \library\Config::DB_CONFIG_PASSWORD => 'Vao5yBkpgkoRbEk0d9BDaMQU9bHeay1w',
    ),
);
/**
 * db.phpで'default'に指定した接続情報以外で接続するテーブル名と、
 * そのテーブルに接続するための接続情報のキーを設定する
 * array(
 *     'テーブル名' => '接続情報のキー',
 * );
 */
$_table_config = array(
    'uu_daily' => 'statistics',
    'user_memo' => 'admin',
    'user_ban' => 'admin',
    'user_ban_history' => 'admin',
);
/**
 * PUSHを送信する為に必要な情報
 * array(
 *     'ios' => array(
 *        'send_retry_times' => int 失敗時、再送信を試行する回数,
 *        'environment' => string "sandbox" もしくは "production",
 *        'provider_certification_authority' => string 証明書へのファイルパス,
 *        'root_certification_authority' => string 証明書へのファイルパス,
 *     ),
 *     'android' => array(
 *         'api_key' => string API キー,
 *     ),
 * );
 */
$_push_config = array(
    'ios' => array(
        'send_retry_times' => 1, //失敗時、再送信を試行する回数
        'environment' => "production", //"sandbox" もしくは "production"
        'provider_certification_authority' => '/home/homepage/html/public/_pem/maketalk.production.pem', //証明書へのファイルパス
        'root_certification_authority' => '', //証明書へのファイルパス,
    ),
    'android' => array(
        'api_key' => 'AIzaSyDjunz7AdnVu2pThcua-hx98Dh5RF3dJ0I',//string API キー
    ),
);