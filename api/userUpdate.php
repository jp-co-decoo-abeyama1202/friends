<?php
/**
 * ユーザ情報を更新する
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id' => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'login_time' => 1,
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //受信jsonパラメータ
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    if(is_null($token)) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromToken($token);
    //クエリ発行
    $id = (int)$user['id'];
    $uuAdd = false;
    $storage->beginTransaction();
    $result1 = $result2 = $result3 = true;
    $now = time();
    //Userの更新
    $keys = array('name','sex','age','country','area','request','publishing','profile','device','state','login_time','image','push_id');
    //push_idのNULL対策
    if(array_key_exists('push_id',$params) && is_null($params['push_id'])) {
        $params['push_id'] = '';//0バイト文字に変換
    }
    $values = getKeyValues($keys,$params);
    if($values) {
        if(isset($values['name'])) {
            $values['name'] = trim($values['name']);
            $values['name'] = preg_replace('/[\n|\t|\r]/','',$values['name']);
            if(!$values['name']) {
                throw new InvalidArgumentException();
            }
        }
        if(isset($values['login_time'])) {
            $values['login_time'] = $now;
            $uuAdd = true;
        }
        $values['update_time'] = $now;
        $result1 = $storage->User->updatePrimaryOne($values,$id);
    }
    if(IS_TEST) {
        error_log("==== user update ====");
        error_log("id : $id");
        foreach($values as $key => $value) {
            error_log($key." => " .$value);
        }
    }
    //UserCommentの登録・更新
    $keys = array('id','title','text');
    $values = getKeyValues($keys,$params,'comment_');
    if($values && count($values) === 3) { //コメントの更新は3つ全て揃ってないと行わない
        $values['comment_id'] = $values['id'];
        unset($values['id']);
        $values['user_id'] = $id;
        $values['create_time'] = $now;
        $values['update_time'] = $now;
        $result2 = $storage->UserComment->insertOne($values);
    }
    //UserOptionの更新
    $keys = array('sex','min_age','max_age','country','area','push_friend','push_chat','view_friend','view_refuse');
    $values = getKeyValues($keys,$params,'option_');
    if($values) {
        $values['update_time'] = $now;
        $result3 = $storage->UserOption->updatePrimaryOne($values,$id);
    }
    if(!$result1 || !$result2 || !$result3) {
        throw new ErrorException();
    }
    
    //UUの集計
    if($uuAdd) {
        $storage->UuDaily->add($id);
    }
    $storage->commit();
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}



