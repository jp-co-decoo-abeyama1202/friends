<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library\admin;
class Model_User extends \library\Model_User {
    public static $_ageList = array(
        parent::AGE_ALL => '指定なし',
        parent::AGE_TEENS_EARLY => '10代前半',
        parent::AGE_TEENS_MID => '10代半ば',
        parent::AGE_TEENS_LATE => '10代後半',
        parent::AGE_TWENTIES_EARLY => '20代前半',
        parent::AGE_TWENTIES_MID => '20代半ば',
        parent::AGE_TWENTIES_LATE => '20代後半',
        parent::AGE_THIRTIES_EARLY => '30代前半',
        parent::AGE_THIRTIES_MID => '30代半ば',
        parent::AGE_THIRTIES_LATE => '30代後半',
        parent::AGE_FORTIES => '40代',
        parent::AGE_FIFTIES => '50代',
    );
            
    public static $_sexList = array(
        parent::SEX_ALL => '指定なし',
        parent::SEX_MAN => '男性',
        parent::SEX_WOMAN=> '女性',
    );
    
    public static $_countryList = array(
        parent::COUNTRY_ALL => '指定なし',
        parent::COUNTRY_AUSTRALIA => 'オーストラリア',
        parent::COUNTRY_CANADA=> 'カナダ',
        parent::COUNTRY_CHINA=> '中国',
        parent::COUNTRY_INDIA=> 'インド',
        parent::COUNTRY_JAPAN=> '日本',
        parent::COUNTRY_KOREA=> '韓国',
        parent::COUNTRY_TAIWAN=> '台湾',
        parent::COUNTRY_USA=> 'アメリカ',
    );
    
    public static $_requestList = array(
        parent::REQUEST_VALID => '申請受付',
        parent::REQUEST_INVALID => '申請拒否'
    );
    
    public static $_publishingList = array(
        parent::PUBLISHING_VALID => '全体公開',
        parent::PUBLISHING_INVALID => 'フレンドのみ公開'
    );
    
    public static $_deviceList = array(
        parent::DEVICE_IOS => 'iOS',
        parent::DEVICE_ANDROID => 'Android',
    );
    
    public static $_stateList = array(
        parent::STATE_VALID => '通常',
        parent::STATE_INVALID => '削除',
    );
    
    /**
     * 関係：フレンド
     */
    const RELATION_FRIEND = 1;
    /**
     * 関係：ブロックしてる
     */
    const RELATION_BLOCK = 2;
    
    /**
     * 関係：ブロックされてる
     */
    const RELATION_BLOCKER = 3;
    
    /**
     * 関係：フレンド解除
     */
    const RELATION_CANCELL = 4;
        
    /**
     * 関係：申請拒否(自分から）
     */
    const RELATION_REFUSE_FROM = 10;
    
    /**
     * 関係：申請拒否(相手から）
     */
    const RELATION_REFUSE_TO = 11;
    
    /**
     * 関係：申請中(自分から)
     */
    const RELATION_PENDING_FROM = 20;
    
    /**
     * 関係：申請中(相手から)
     */
    const RELATION_PENDING_TO = 21;
    
    /**
     * 関係：何もなし
     */
    const RELATION_NONE = 0;
    
    public static $_relationList = array(
        self::RELATION_NONE => '何もなし',
        self::RELATION_FRIEND => 'フレンド',
        self::RELATION_BLOCK => 'ブロックしている',
        self::RELATION_BLOCKER => 'ブロックされている',
        self::RELATION_CANCELL => 'フレンド解除',
        self::RELATION_REFUSE_FROM => '申請拒否(自分から)',
        self::RELATION_REFUSE_TO => '申請拒否(相手から)',
        self::RELATION_PENDING_FROM => '申請中(自分から)',
        self::RELATION_PENDING_TO => '申請中(相手から)',
     );
    
    /**
     * 登録者数を取得する
     * @param type $time
     * @return int
     */
    public function getRegisterUserCount($time = null)
    {
        $now = time();
        if(!$time) {
            $time = $now;
        }
        if(!is_numeric($time)) {
            //数字じゃないのでstrtotime
            $time = strtotime($time);
        }
        //指定時間の0時～23:59:59までに登録した人を集計
        $start = strtotime(date('Y/m/d 00:00:00',$time));
        $end = strtotime(date('Y/m/d 23:59:59',$time));
        $ret = array(
            'total' => 0,
            'device_' . parent::DEVICE_IOS => 0,
            'device_' . parent::DEVICE_ANDROID => 0,
            'sex_' . parent::SEX_ALL => 0,
            'sex_' . parent::SEX_MAN => 0,
            'sex_' .parent::SEX_WOMAN => 0,
        );
        if($now >= $start && $now <= $end) {
            //今日の分
            $sql = 'SELECT device,sex FROM user WHERE create_time BETWEEN ? AND ?';
            $stmt = $this->_con->prepare($sql);
            $stmt->execute(array($start,$end));
            while($row = $stmt->fetch()) {
                $ret['total']++;
                $ret['device_'.$row['device']]++;
                $ret['sex_'.$row['sex']]++;
            }
        } else if($start >= $now){
            //未来の分
            //ない
        } else {
            //過去の分
            //集計済みデータを引っ張る
            $con = $this->_storage->getConnection("statistics");
            $stmt = $con->prepare('SELECT count FROM daily_register WHERE regist_date = ?');
            $stmt->execute(array($start));
            $ret['total'] = (int)$stmt->fetchColumn();
            //device
            $stmt = $con->prepare('SELECT type,count FROM daily_register_device WHERE regist_date = ?');
            $stmt->execute(array($start));
            while($row = $stmt->fetch()) {
                $ret['device_'.$row['type']] = (int)$row['count'];
            }
            //sex
            $stmt = $con->prepare('SELECT type,count FROM daily_register_sex WHERE regist_date = ?');
            $stmt->execute(array($start));
            while($row = $stmt->fetch()) {
                $ret['sex_'.$row['type']] = (int)$row['count'];
            }
        }
        return $ret;
    }
    
    /**
     * ユーザ情報丸々取得
     * @param type $userId
     * @return type
     */
    public function get($userId)
    {
        $user = $this->primaryOne($userId);
        $user['token'] = $this->_storage->UserToken->getToken($userId);
        //オプション
        $option = $this->_storage->UserOption->primaryOne($userId);
        //コメント一覧取得
        $comment = $this->_storage->UserComment->getCommentList($userId);
        //写真取得
        $photo = $this->_storage->UserPhoto->getPhotoList($userId);
        $ret = array(
            'user' => $user,
            'option' => $option,
            'photos'  => $photo,
            'comments' => $comment,
        );    
        return $ret;
    }
    
    /**
     * 検索＆一覧の内容を取得する
     * @param array $searchValue
     * @param array
     */
    public function search($option)
    {
        $binds = array();
        $where = array();
        
        if(isset($option[':id'])) {
            $binds[':id'] = $option[':id'];
            if(is_numeric($option[':id'])) {
                $where[] = 'user.id=:id';
            } else {
                $where[] = 'user_token.token = :id';
            }
        }
        if(isset($option[':name']) && $option[':name']) {
            $binds[':name'] = '%'.$option[':name'].'%';
            $where[] = 'name LIKE :name';
        }
        if(isset($option[':sex']) && $option[':sex'] != self::SEX_ALL) {
            $binds[':sex'] = $option[':sex'];
            $where[] = 'sex=:sex';
        }
        if(isset($option[':min_age']) && $option[':min_age'] != self::AGE_ALL) {
            $binds[':min_age'] = $option[':min_age'];
            $where[] = 'age>=:min_age';
        }
        if(isset($option[':max_age']) && $option[':max_age'] != self::AGE_ALL) {
            $binds[':max_age'] = $option[':max_age'];
            $where[] = 'age<=:max_age';
        }
        if(isset($option[':country']) && $option[':country'] != self::COUNTRY_ALL) {
            $binds[':country'] = $option[':country'];
            $where[] = 'country=:country';
        }
        if(isset($option[':area']) && $option[':area'] != self::AREA_ALL) {
            $binds[':area'] = $option['area'];
            $where[] = 'area=:area';
        }
        if(isset($options[':request']) && $options[':request'] != self::NO_SELECT) {
            $binds[':request'] = $option['request'];
            $where[] = 'request=:request';
        }
        if(isset($options[':publishing']) && $options[':publishing'] != self::NO_SELECT) {
            $binds[':publishing'] = $option['publishing'];
            $where[] = 'publishing=:publishing';
        }
        if(isset($options[':profile'])) {
            $binds[':profile'] = '%'.$option['profile'].'%';
            $where[] = 'profile LIKE :profile';
        }
        if(isset($options[':device']) && $options[':device'] != self::NO_SELECT) {
            $binds[':device'] = $option['device'];
            $where[] = 'device=:device';
        }
        if(isset($options[':state']) && $options[':state'] != self::NO_SELECT) {
            $binds[':state'] = $option['state'];
            $where[] = 'state=:state';
        }
        if(isset($option[':min_create_time']) && $option[':min_create_time']) {
            $binds[':min_create_time'] = $option[':min_create_time'];
            $where[] = 'create_time>=:min_create_time';
        }
        if(isset($option[':max_create_time']) && $option[':max_create_time']) {
            $binds[':max_create_time'] = $option[':max_create_time'];
            $where[] = 'create_time<=:max_create_time';
        }
        
        $sql = 'SELECT * FROM user';
        $sql.= ' LEFT JOIN user_token ON user.id = user_token.id';
        $sql.= ' WHERE 1=1 ';
        if(!empty($where)) {
            $sql.= ' AND ' . implode(' AND ',$where);
        }
        /*
        switch($sort) {
            case 'id_A':
                $sql.= ' ORDER BY user.id ASC';
                break;
            case 'id_D':
                $sql.= ' ORDER BY user.id DESC';
                break;
            case 'name':
                $sql.= ' ORDER BY name ASC';
                break;
            case 'name':
                $sql.= ' ORDER BY name DESC';
                break;
            case 'age_A':
                $sql.= ' ORDER BY age ASC';
                break;
            case 'age_D':
                $sql.= ' ORDER BY age DESC';
                break;
            case 'area':
                $sql.= ' ORDER BY area ASC';
                break;
            case 'create_time_A':
                $sql.= ' ORDER BY create_time ASC';
                break;
            case 'create_time_D':
                $sql.= ' ORDER BY create_time DESC';
                break;
            default:
                $sql.= ' ORDER BY login_time DESC';
                break;
        }
         */
        
        $stmt = $this->_con->prepare($sql);
        foreach($binds as $key => $value) {
            $stmt->bindValue($key,$value);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 相手と自分の関係性を取得
     * @param type $userId
     * @param type $friendId
     * @return type
     */
    public function getRelation($userId,$friendId)
    {
        //まずフレンドかどうかチェック
        if($this->_storage->UserFriend->checkFriend($userId,$friendId)) {
            return self::RELATION_FRIEND;
        }
        //ブロックチェック
        if($this->_storage->UserBlock->check($userId,$friendId)) {
            return self::RELATION_BLOCK;
        }
        if($this->_storage->UserBlock->check($friendId,$userId)) {
            return self::RELATION_BLOCKER;
        }
        //もともとフレンドだったかチェック
        $friendsId = $this->_storage->Friends->getId($userId,$friendId);
        if($friendsId) {
            //もともとフレンドでブロックでもないなら申請解除
            return self::RELATION_CANCELL;
        }
        //フレンドですらない
        //申請状況チェック
        $relation = self::RELATION_NONE;
        $requestFrom = $this->_storage->UserRequestFrom->get($userId,$friendId);
        $requestTo = $this->_storage->UserRequestTo->get($friendId,$userId);
        if($requestFrom) {
            //自分から申請してる
            if($requestFrom['state'] == \library\Model_UserRequestFrom::STATE_REFUSE) {
                //相手が拒否してる
                return self::RELATION_REFUSE_TO;
            }
            if($requestFrom['state'] == \library\Model_UserRequestFrom::STATE_PENDING) {
                //自分からは申請中
                $relation = self::RELATION_PENDING_FROM;
            }
        }
        if($requestTo) {
            //相手から申請がある。
            if($requestTo['state'] == \library\Model_UserRequestFrom::STATE_REFUSE) {
                //自分が拒否
                return self::RELATION_REFUSE_FROM;
            }
            if($requestTo['state'] == \library\Model_UserRequestFrom::STATE_PENDING) {
                //相手からは申請中
                $relation = self::RELATION_PENDING_TO;
            }
        }
        return $relation;
    }
}

