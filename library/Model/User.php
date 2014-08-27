<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_User extends Model {
    //性別
    const SEX_ALL = 0;
    const SEX_MAN = 1;
    const SEX_WOMAN = 2;
    //年齢範囲
    const AGE_ALL = 0;
    const AGE_TEENS_EARLY = 1;
    const AGE_TEENS_MID = 2;
    const AGE_TEENS_LATE = 3;
    const AGE_TWENTIES_EARLY = 4;
    const AGE_TWENTIES_MID = 5;
    const AGE_TWENTIES_LATE = 6;
    const AGE_THIRTIES_EARLY = 7;
    const AGE_THIRTIES_MID = 8;
    const AGE_THIRTIES_LATE = 9;
    const AGE_FORTIES = 10;
    const AGE_FIFTIES = 11;
    //国
    const COUNTRY_ALL = 0;
    const COUNTRY_AUSTRALIA = 1;
    const COUNTRY_CANADA = 2;
    const COUNTRY_CHINA = 3;
    const COUNTRY_INDIA = 4;
    const COUNTRY_JAPAN = 5;
    const COUNTRY_KOREA = 6;
    const COUNTRY_TAIWAN = 7;
    const COUNTRY_BRITAIN = 8;
    const COUNTRY_USA = 9;
    //地域
    const AREA_ALL = 0;
    //友だち申請可否
    const REQUEST_VALID = 1;
    const REQUEST_INVALID = 2;
    //公開設定　公開・非公開
    const PUBLISHING_VALID = 1;
    const PUBLISHING_INVALID = 2;
    //端末のOS
    const DEVICE_IOS = 1;
    const DEVICE_ANDROID = 2;
    //状態
    const STATE_VALID = 1;
    const STATE_INVALID = 2;
    
    //新規登録時ステータス
    const ADD_SUCCESS = 1;
    const ADD_DUPLICATE = 10;
    
    const SEARCH_DEFAULT = 30;
    
    protected $_table_name = 'user';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'udid' => \PDO::PARAM_STR,
        'name' => \PDO::PARAM_STR,
        'sex'  => \PDO::PARAM_INT,
        'age'  => \PDO::PARAM_INT,
        'country'  => \PDO::PARAM_INT,
        'area'  => \PDO::PARAM_INT,
        'request'  => \PDO::PARAM_INT,
        'publishing'  => \PDO::PARAM_INT,
        'push_id' => \PDO::PARAM_STR,
        'profile' => \PDO::PARAM_STR,
        'device'  => \PDO::PARAM_INT,
        'state'  => \PDO::PARAM_INT,
        'image' => \PDO::PARAM_LOB,
        'create_time'  => \PDO::PARAM_INT,
        'login_time'  => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    
    /**
     * primary_keyを元にレコードを取得
     * @param type $ids
     * @return type
     */
    public function primary($ids)
    {
        $ret = parent::primary($ids);
        $bans = $this->_storage->UserBan->primary(array_keys($ret));
        $removes = array();
        $now = time();
        foreach($bans as $id => $ban) {
            $available = (int)$ban['available'];
            if($available === Model_UserBan::AVAILABLE_FALSE) {
                $removes[] = $id;
                continue;
            }
            $end = (int)$ban['end_time'];
            if($end && $end < $now) {
                $removes[] = $id;
                continue;
            }
            //削除状態にする
            $ret[$id]['state'] = self::STATE_INVALID;
        }
        $this->_storage->UserBan->removes($removes);
        return $ret;
    }
    
    /**
     * udidからユーザデータを取得する
     * @param string $udid
     * @return Array|null
     */
    public function getDataFromUdid($udid)
    {
        $query = 'SELECT id FROM ' . $this->_table_name .' WHERE udid = :udid';
        $stmt = $this->_con->prepare($query);
        $stmt->bindValue(':udid',$udid,$this->_data_types['udid']);
        $stmt->execute();
        $id = (int)$stmt->fetchColumn();
        if($id) {
            return $this->primaryOne($id);
        }
        return null;
    }
    
    /**
     * tokenからユーザデータを取得する
     */
    public function getDataFromToken($token)
    {
        if(!$token) {
            throw new \InvalidArgumentException();
        }
        $userId = $this->_storage->UserToken->getIdFromToken($token);
        if($userId) {
            return $this->primaryOne($userId);
        }
        return null;
    }
    
    /**
     * ユーザIDから一覧の内容を取得する
     * @param int $userId 
     * @param int $offset = 0
     * @param int $count = 30
     * @param bool $addBlocker ブロックされてる人も含めるか
     * @param bool $addFriend フレンドも含めるか
     */
    public function search($userId,$offset = 0,$count = self::SEARCH_DEFAULT,$addBlocker=false,$addFriend=false)
    {
        $userId = (int)$userId;
        $option = $this->_storage->UserOption->primaryOne($userId);
        if(!$option) {
            return array();
        }
        $binds = array();
        $where = array();
        if($option['sex'] != self::SEX_ALL) {
            $binds[':sex'] = $option['sex'];
            $where[] = 'sex=:sex';
        }
        if($option['min_age'] != self::AGE_ALL) {
            $binds[':min_age'] = $option['min_age'];
            $where[] = 'age>=:min_age';
        }
        if($option['max_age'] != self::AGE_ALL) {
            $binds[':max_age'] = $option['max_age'];
            $where[] = 'age<=:max_age';
        }
        if($option['country'] != self::COUNTRY_ALL) {
            $binds[':country'] = $option['country'];
            $where[] = 'country=:country';
        }
        if($option['area'] != self::AREA_ALL) {
            $binds[':area'] = $option['area'];
            $where[] = 'area=:area';

        }
        if(!$addBlocker) {
            //ブロックされているユーザを除外
            $blockerIds = $this->_storage->UserBlocker->getBlockerIds($userId);
            if($blockerIds) {
                $where[] = 'user.id NOT IN ('.implode(',',$blockerIds).')';
            }
        }
        if(!$addFriend) {
            //フレンドを除外
            $friendIds = $this->_storage->UserFriend->getFriendsIds($userId);
            if($friendIds) {
                $where[] = 'user.id NOT IN ('.implode(',',$friendIds).')';
            }
        }
        $binds[':user_id'] = $userId;
        $sql = 'SELECT token as id,name,sex,age,country,area,request,publishing,profile,device,image,login_time FROM user';
        $sql.= ' LEFT JOIN user_token ON user.id = user_token.id';
        $sql.= ' WHERE user.id != :user_id AND state = ' . self::STATE_VALID . ' ';
        if(!empty($where)) {
            $sql.= ' AND ' . implode(' AND ',$where);
        }
        $sql.= ' ORDER BY login_time DESC';
        $sql.= ' LIMIT ' . $offset . ' , ' .$count;
        
        $stmt = $this->_con->prepare($sql);
        foreach($binds as $key => $value) {
            $stmt->bindValue($key,$value);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * RequestListで必要なデータを返す
     * return array(
     *     {user.id} => array({データ}),
     *   ...
     *     {user.id} => array({データ})
     * );
     * @param type $ids
     * @return array
     */
    public function getDataFromRequestList($ids)
    {
        $sql = 'SELECT user.id as user_id,token as id,name,sex,age,country,area,request,publishing,profile,device,state,image,login_time FROM user';
        $sql.= ' LEFT JOIN user_token ON user.id = user_token.id';
        $sql.= ' WHERE user.id IN ('.implode(',',$ids).')';
        $stmt = $this->_con->prepare($sql);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $ret = array();
        foreach($list as $l) {
            $userId = (int)$l['user_id'];
            unset($l['user_id']);
            $ret[$userId] = $l;
        }
        return $ret;
    }
}

