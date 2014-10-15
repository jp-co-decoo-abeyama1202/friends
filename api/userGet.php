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
        'user_id'   => 'UZHbrrQLmaaqJHVyuALbbXY69Hwgst',
        'view_id' => 'fmnKYLQe8dteQYfvM1a9fOdzMomWrx',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $vtoken = isset($params['view_id']) ? $params['view_id'] : null;
    if(is_null($token)||is_null($vtoken)) {
        throw new InvalidArgumentException();
    }
    

    //閲覧先
    $user = $storage->User->getDataFromToken($vtoken);
    $self = false;
    //閲覧者（自分）
    if($vtoken === $token) {
        $my = $user;
        $self = true;
    } else {
        $my = $storage->User->getDataFromToken($token);
    }
    $id = (int)$user['id'];
    $myId = (int)$my['id'];
    //画像一覧取得
    $user['photo'] = $storage->UserPhoto->getPhotoList($id);
    //コメント一覧取得
    $user['comment'] = $storage->UserComment->getCommentList($id);
    //ブロックしているか
    $user['to_block'] = $storage->UserBlock->check($myId,$id);
    //ブロックされているか
    $user['from_block'] = $storage->UserBlock->check($id,$myId);
    //リクエスト情報取得
    //このユーザに申請しているか
    $to_request = $storage->UserRequestFrom->get($myId,$id);
    $user['to_request'] = $to_request ? (int)$to_request['state'] : null;
    $user['to_message'] = null;
    if($to_request) {
        $requestMessage = $storage->UserRequestMessage->getMessage((int)$to_request['message_id'],$myId);
        if($requestMessage) {
            $user['to_message'] = $requestMessage['message'];
        }
    }
    //このユーザから申請されているか
    $from_request = $storage->UserRequestFrom->get($id,$myId);
    $user['from_request'] = $from_request ? (int)$from_request['state'] : null;
    $user['from_message'] = null;
    if($from_request) {
        $requestMessage = $storage->UserRequestMessage->getMessage((int)$from_request['message_id'],$id);
        if($requestMessage) {
            $user['from_message'] = $requestMessage['message'];
        }
    }
    //フレンド情報取得
    $user['friend'] = $storage->UserFriend->checkFriend($myId,$id);
    //IDにトークンセット
    $user['id'] = $vtoken;
    //不要データの削除
    $unsetList = array('udid','push_id');
    //フレンドのみにその他情報を公開する場合
    if(!$user['friend'] && !$self && $user['publishing'] == \library\Model_User::PUBLISHING_INVALID) {
        $user['comment'] = array();
    }
    foreach($unsetList as $key) {
        unset($user[$key]);
    }
    return \library\Response::json($user);
} catch(Exception $e) {
    return \library\Response::error($e);
}
