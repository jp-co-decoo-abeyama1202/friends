<?php
/**
 * ユーザ情報を登録する
 * Created by PhpStorm.
 * User: ishii
 * Date: 14/07/25
 * Time: 19:12
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'udid' => '00000000000000000000000000000004',
        'name' => 'テスト太郎2_4',
        'sex' => \library\Model_User::SEX_MAN,
        'age' => \library\Model_User::AGE_ALL,
        'country' => \library\Model_User::COUNTRY_JAPAN,
        'area' => 51,
        'push_id' => 'test_push_id',
        'device' => \library\Model_User::DEVICE_IOS
    );
    $_POST['params'] = json_encode($test_params);
}
*/
//受信jsonパラメータ
$json = isset($_POST['params']) ? $_POST['params'] : '';
$params = json_decode($json,true);

//jsonデータのうち、必要なデータのキー
$list = array(
    'udid','name','sex','age','country','area','push_id','device'
);

//重複チェック
$udid = isset($params['udid']) ? $params['udid'] : null;
if(is_null($udid)) {
    error_log('userAddError:[udid] is not found');
    http_response_code(400);
    exit;
}

try {
    //udidからデータ取得
    $user = $storage->User->getDataFromUdid($udid);
    if($user) {
        //登録済みユーザ
        $token = $storage->UserToken->getToken((int)$user['id']);
        echo json_encode(array('status'=>\library\Model_User::ADD_DUPLICATE,'user_id'=>$token));
        http_response_code(200);
        exit;
    }
} catch (PDOException $ex) {
    error_log($e->getMessage());
    http_response_code(400);
    exit;
}
//INSERT
$now = time();
$keys = array(
    'udid', 'name', 'sex', 'age', 'country', 'area', 'push_id', 'device', 'login_time', 'create_time', 'update_time'
);
$values = array();
foreach($keys as $key) {
    $value = isset($params[$key]) ? $params[$key] : null;
    if(!is_null($value)) {
        $values[$key] = $value;
    } else if(in_array($key,array('login_time','create_time','update_time'))) {
        //登録時間、更新時間、ログイン時間
        $values[$key] = $now;
    } else {
        error_log('userAddError:必要なパラメータ['.$key.']が足りません');
        http_response_code(400);
        exit;
    }
}

//user_optionを登録
$sex = $params['sex'];
//逆の性別をセット
if($sex == \library\Model_User::SEX_MAN) {
    $sex = \library\Model_User::SEX_WOMAN;
} else if($sex == \library\Model_User::SEX_WOMAN) {
    $sex = \library\Model_User::SEX_MAN;
}
$options = array(
    'sex' => $sex,
    'min_age' => \library\Model_User::AGE_ALL,
    'max_age' => \library\Model_User::AGE_ALL,
    'country' => $params['country'],
    'area' => \library\Model_User::AREA_ALL,
    'push_friend' => \library\Model_UserOption::FLAG_ON,
    'push_chat' => \library\Model_UserOption::FLAG_ON,
    'update_time' => $now,
);

//クエリ発行
$storage->beginTransaction();
try {
    $storage->User->insertOne($values);
    $userId = (int)$storage->User->lastInsertId();
    if(!$userId) {
        throw new InvalidArgumentException();
    }
    $options['user_id'] = $userId;
    //option に insert
    $storage->UserOption->insertOne($options);
    //tokenを登録
    $token = $storage->UserToken->create($userId);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(400);
    $storage->rollBack();
    exit;
}

$storage->commit();
echo json_encode(array('status'=>\library\Model_User::ADD_SUCCESS,'user_id'=>$token));
return http_response_code(200);