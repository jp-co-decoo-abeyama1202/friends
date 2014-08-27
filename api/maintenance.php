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
$json = json_encode(
        array(
            'maintenance' => true,
            'text' => 'メンテナンス中です',
        )
);
echo $json;
return http_response_code(503);