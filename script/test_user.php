<?php
require_once(__DIR__ . '/_header.php');
try{
    $start = 0;
    $last = 100;
    $storage->beginTransaction();
    for($i=$start;$i<=$last;$i++) {
        $values = array(
            'udid' => sprintf('%010d',$i),
            'name' => 'テストユーザ:' . sprintf('%010d',$i),
            'sex' => array_rand(array(\library\Model_User::SEX_ALL,\library\Model_User::SEX_MAN,\library\Model_User::SEX_WOMAN)),
            'age' => array_rand(array(\library\Model_User::AGE_ALL,\library\Model_User::AGE_TEENS_EARLY,\library\Model_User::AGE_TEENS_MID,\library\Model_User::AGE_TEENS_LATE,\library\Model_User::AGE_TWENTIES_EARLY,\library\Model_User::AGE_TWENTIES_MID,\library\Model_User::AGE_TWENTIES_LATE,\library\Model_User::AGE_THIRTIES_EARLY,\library\Model_User::AGE_THIRTIES_MID,\library\Model_User::AGE_THIRTIES_LATE,\library\Model_User::AGE_FORTIES,\library\Model_User::AGE_FIFTIES)),
            'country' => \library\Model_User::COUNTRY_JAPAN,
            'area' => \library\Model_User::AREA_ALL,
            'push_id' => '',
            'device' => array_rand(array(\library\Model_User::DEVICE_IOS,\library\Model_User::DEVICE_ANDROID)),
            'login_time' => time(),
            'create_time' => time(),
            'update_time' => time(),
        );
        //user_optionを登録
        $sex = $values['sex'];
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
            'country' => $values['country'],
            'area' => \library\Model_User::AREA_ALL,
            'push_friend' => \library\Model_UserOption::FLAG_OFF,
            'push_chat' => \library\Model_UserOption::FLAG_OFF,
            'update_time' => time(),
        );
        //クエリ発行
        
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
    }
    $storage->commit();
} catch(Exception $e) {
    error_log($e);
    if($storage->isTransaction()) {
        $storage->rollback();
    }
}