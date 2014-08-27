<?php
require_once(__DIR__."/_header.php");
//受信jsonパラメータ
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);

//存在チェック
$userId = isset($params['user_id']) ? $params['user_id'] : null;
$imgData = isset($params['img_data']) ? $params['img_data'] : null;
if(is_null($userId)) {
    error_log('userImageAdd Error:[user_id] is not found');
    http_response_code(400);
    exit;
}
if(is_null($imgData)) {
    error_log('userImageAdd Error:[img_data] is not found');
    http_response_code(400);
    exit;
}
try {
    $user = $storage->User->primaryOne($userId);
    if(!$user) {
        //存在しないユーザ
        error_log('userImageDelete Error：this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (Exception $ex) {
    error_log($ex->getMessage());
    http_response_code(400);
    exit;
}

$id = (int)$user['id'];
//img_dataをひも解いて何を行う処理か判断する
$type = '';
$no = 0;
if(preg_match('/^img_(image|photo){1}_*(\d)*$/',$imgData,$matches)) {
    $type = $matches[1];
    if($type === 'photo') {
        $no = isset($matches[2]) ? (int)$matches[2] : 0;
        if(!$no) {
            error_log('img_delete.php Error:img_data is valid A > ' .$userId . '[' .$imgData . ']');
            http_response_code(400);
            exit;
        }
    }
} else {
    error_log('img_delete.php Error:img_data is valid B > ' .$userId . '[' .$imgData . ']');
    http_response_code(400);
    exit;
}

//クエリ発行
$storage->beginTransaction();
try {
    if($type === 'image') {
        $values = array(
            'image' => null,
            'update_time' => time(),
        );
        //Userの更新
        $result = $storage->User->updatePrimaryOne($values,$userId);
    } else if ($type === 'photo') {
        $result = $storage->UserPhoto->delete($userId,$no);
    }
} catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
if(!$result) {
    error_log('img_delete Error：delete failed > '.$userId . '[' .$imgData . ']');
    http_response_code(400);
    exit;
}
$storage->commit();
echo json_encode(array(
    'user_id' => $userId,
    'img_data' => $imgData,
));
return http_response_code(200);
