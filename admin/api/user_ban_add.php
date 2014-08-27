<?php
require_once(__DIR__."/_header.php");
//受信jsonパラメータ
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);

//存在チェック
$userId = isset($params['user_id']) ? $params['user_id'] : null;
$reason = isset($params['reason']) ? $params['reason'] : null;
$times = isset($params['ban_time']) ? $params['ban_time'] : null;
if(is_null($userId)) {
    error_log('user_ban_add error:[user_id] is not found');
    error_log('params:' . $json);
    http_response_code(400);
    exit;
}
if(is_null($reason)&&$reason) {
    //理由は必須
    error_log('user_ban_add error:[$reason] is not found');
    error_log('params:' . $json);
    http_response_code(400);
    exit;
}
if(is_null($times)) {
    error_log('user_ban_add error:[$times] is not found');
    error_log('params:' . $json);
    http_response_code(400);
    exit;
}

try {
    $user = $storage->User->primaryOne($userId);
    if(!$user) {
        //存在しないユーザ
        error_log('user_ban_add error:this user is not found > '.$userId);
        http_response_code(400);
        exit;
    }
} catch (Exception $ex) {
    error_log($ex->getMessage());
    http_response_code(400);
    exit;
}
//時間情報を取得する
$now = time();
$start = $end = 0;
if($times) {
    $t = explode(' - ',$times);
    if(count($t) === 2) {
        $start = strtotime($t[0]);
        $end = strtotime($t[1]);
    }
}
//過去分は登録出来ないように
//0の場合は無期限なので注意
if($end && $end < $now) {
    error_log("user_ban_add error:past data");
    return http_response_code(400);
}

//現在執行中のBAN情報があるか
$ban = $storage->UserBan->primaryOne($userId);
$isDelete = false;
$isUpdate = false;
if($ban) {
    //有効状態のBAN情報がある。
    $now_start = (int)$ban['start_time'];
    $now_end = (int)$ban['end_time'];
    if($now_start === $start && $now_end === $end) {
        $isUpdate = true;
    } else if($ban['available'] == \library\Model_UserBan::AVAILABLE_FALSE) {
        $isDelete = true;
    } else {
        //上書きチェック
        if(!$now_end) {
            //無期限有効
            if(!$now_start) {
                //開始終了共に無期限なので登録する必要なし
                error_log("user_ban_add error:not required this data");
                return http_response_code(400);
            }
            if($now_start <= $start) {
                //開始時間的に含まれてるデータなので登録する必要なし
                error_log("user_ban_add error:not required this data");
                return http_response_code(400);
            }
        } else {
            if($now_end < $now) {
                //既に期限切れ
                $isDelete = true;
            }
        }
        if($start && $start > $now && $start > $now) {
            //予約は1件まで
            error_log("user_ban_add error:reservation data is only 1");
            return http_response_code(400);
        }
    }
}

//クエリ発行
$storage->beginTransaction();
try {
    $values = array(
        'id' => $userId,
        'available' => \library\Model_UserBan::AVAILABLE_TRUE,
        'reason' => $reason,
        'start_time' => $start,
        'end_time' => $end,
        'create_time' => $now,
        'update_time' => $now,
    );
    
    if($isDelete) {
        $dValues = array(
            'user_id' => $userId,
            'reason' => $ban['reason'],
            'start_time' => $ban['start_time'],
            'end_time' => $ban['end_time'],
            'create_time' => $now,
        );
        $storage->UserBanHistory->insertOne($dValues);
    }
    //データ更新
    $storage->UserBan->insertOne($values);
    
} catch(Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}

$storage->commit();
return http_response_code(200);
