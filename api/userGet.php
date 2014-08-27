<?php
/**
 * ユーザ情報取得
 * Created by PhpStorm.
 * User: ishii
 * Date: 14/07/29
 * Time: 13:21
 */

require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id'   => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'view_id' => 'uEA2J4CFcUSGxpKHw5605GZfT4WlyJ',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
//json受け取り
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);
//存在チェック
$token = isset($params['user_id']) ? $params['user_id'] : null;
$vtoken = isset($params['view_id']) ? $params['view_id'] : null;
if(is_null($token)) {
    error_log('userGet Error:[user_id] is not found');
    http_response_code(400);
    exit;
}
if(is_null($vtoken)) {
    error_log('userGet Error:[view_id] is not found');
    http_response_code(400);
    exit;
}
try {
    //閲覧先
    $viewId = $storage->UserToken->getIdFromToken($vtoken);
    if(!$viewId) {
        //存在しないユーザ
        error_log('userGet Error：this user is not found > '.$vtoken);
        http_response_code(400);
        exit;
    }
    $user = $storage->User->primaryOne($viewId);
    if(!$user) {
        //存在しないユーザ
        error_log('userGet Error：this user is not found > '.$viewId);
        http_response_code(400);
        exit;
    }
    //閲覧者（自分）
    if($vtoken === $token) {
        $myId = $viewId;
        $my = $user;
    } else {
        $myId = $storage->UserToken->getIdFromToken($token);
        if(!$myId) {
            //存在しないユーザ
            error_log('userGet Error：this user is not found > '.$token);
            http_response_code(400);
            exit;
        }
        $my = $storage->User->primaryOne($myId);
        if(!$myId) {
            //存在しないユーザ
            error_log('userGet Error：this user is not found > '.$myId);
            http_response_code(400);
            exit;
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
$id = (int)$user['id'];
//画像一覧取得
$user['photo'] = $storage->UserPhoto->getPhotoList($id);
//コメント一覧取得
$user['comment'] = $storage->UserComment->getCommentList($id);
//ブロックしているか
$user['to_block'] = $storage->UserBlock->check($myId,$id);
//ブロックされているか
$user['from_block'] = $storage->UserBlock->check($id,$myId);
//リクエスト情報取得
$to_request = $storage->UserRequestFrom->get($myId,$id);
$from_request = $storage->UserRequestFrom->get($id,$myId);
$user['to_request'] = $to_request ? (int)$to_request['state'] : null;
$user['from_request'] = $from_request ? (int)$from_request['state'] : null;
//フレンド情報取得
$user['friend'] = $storage->UserFriend->checkFriend($myId,$id);
//IDにトークンセット
$user['id'] = $vtoken;
//不要データの削除
$unsetList = array('udid','push_id');
foreach($unsetList as $key) {
    unset($user[$key]);
}
echo json_encode($user);
return http_response_code(200);
