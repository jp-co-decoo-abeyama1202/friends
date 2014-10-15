<?php
/**
 * 共通以外の定数を管理する。こちらはテスト用。
 */
const IS_TEST = true;
const BASE_URL = 'http://133.242.23.29/';
const PEM_PATH = '/home/homepage/';

/**
 * DBへの接続情報を設定する。
 * 現状対応しているのがMySqlのみ。
 * 定数や、各値についてはlibrary\Configを参照の事。
 */
$_db_config =  array(
    'default' => array(
        \library\Config::DB_CONFIG_USERNAME => 'root',
        \library\Config::DB_CONFIG_PASSWORD => 'Vao5yBkpgkoRbEk0d9BDaMQU9bHeay1w',
        \library\Config::DB_DSN => 'mysql:host=localhost;dbname=friends;charset=utf8mb4;unix_socket=/mtmp/mysql.sock',
    ),
    'statistics' => array(
        \library\Config::DB_CONFIG_USERNAME => 'root',
        \library\Config::DB_CONFIG_PASSWORD => 'Vao5yBkpgkoRbEk0d9BDaMQU9bHeay1w',
        \library\Config::DB_DSN => 'mysql:host=localhost;dbname=friends_statistics;charset=utf8mb4;unix_socket=/mtmp/mysql.sock',
    ),
    'user_request' => array(
        \library\Config::DB_CONFIG_USERNAME => 'root',
        \library\Config::DB_CONFIG_PASSWORD => 'Vao5yBkpgkoRbEk0d9BDaMQU9bHeay1w',
        \library\Config::DB_DSN => 'mysql:host=localhost;dbname=friends_user_request;charset=utf8mb4;unix_socket=/mtmp/mysql.sock',
    ),
    'user_message' => array(
        \library\Config::DB_CONFIG_USERNAME => 'root',
        \library\Config::DB_CONFIG_PASSWORD => 'Vao5yBkpgkoRbEk0d9BDaMQU9bHeay1w',
        \library\Config::DB_DSN => 'mysql:host=localhost;dbname=friends_user_message;charset=utf8mb4;unix_socket=/mtmp/mysql.sock',
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
    'push' => 'statistics',
    'message' => 'user_message',
    'group_message' => 'user_message',
    'group_message_read' => 'user_message',
    'user_request_from' => 'user_request',
    'user_request_to' => 'user_request',
    'user_request_message' => 'user_request',
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
        'environment' => "sandbox", //"sandbox" もしくは "production"
        'provider_certification_authority' => '/home/homepage/html/public/_pem/maketalk.sandbox.pem', //証明書へのファイルパス
        'root_certification_authority' => '', //ルート証明書へのファイルパス,
    ),
    'android' => array(
        'api_key' => 'AIzaSyDjunz7AdnVu2pThcua-hx98Dh5RF3dJ0I',//string API キー
    ),
);