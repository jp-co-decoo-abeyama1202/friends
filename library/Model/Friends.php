<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_Friends extends Model {
    protected $_table_name = 'friends';
    protected $_primary_key = 'id';
    protected $_users = array();
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'user_id_1'  => \PDO::PARAM_INT,
        'user_id_2'  => \PDO::PARAM_INT,
        'last_message_time' => \PDO::PARAM_INT,
        'last_message_id' => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    
    public function getId($userId1,$userId2)
    {
        $this->checkInt($userId1,$userId2);
        if($userId1>$userId2) {
            $wk = $userId1;
            $userId1 = $userId2;
            $userId2 = $wk;
        }
        $tableName = $this->_table_name;
        $sql = 'SELECT id FROM ' . $tableName . ' WHERE user_id_1 = :user_id_1 AND user_id_2 = :user_id_2';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id_1',$userId1,$this->_data_types['user_id_1']);
        $stmt->bindValue(':user_id_2',$userId2,$this->_data_types['user_id_2']);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    public function getFriendsAll($userId)
    {
        $this->checkInt($userId);
        if(isset($this->_users[$userId])) {
            return $this->_users[$userId];
        }
        
        $tableName = $this->_table_name;
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE user_id_1 = :user_id_1 OR user_id_2 = :user_id_2';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id_1',$userId,$this->_data_types['user_id_1']);
        $stmt->bindValue(':user_id_2',$userId,$this->_data_types['user_id_2']);
        $stmt->execute();
        $ret = array();
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            foreach($this->_data_types as $key => $param) {
                if($param === \PDO::PARAM_INT && isset($row[$key])) {
                    $row[$key] = (int)$row[$key];
                }
            }
            $ret[$row['id']] = $row;
        }
        $this->_users[$userId] = $ret;
        return $ret;
    }
    
    /**
     * user_idに紐付くfriends.idを全件取得する
     * return array(
     *     'friend.id' => 'friend.user.id',
     *     ...
     * );
     * @param type $userId
     * @return array
     */      
    public function getFriendsIds($userId)
    {
        $list = $this->getFriendsAll($userId);
        $ret = array();
        foreach($list as $friendsId => $row) {
            $friendId = $row['user_id_1'];
            if($friendId === $userId) {
                $friendId = $row['user_id_2'];
            }
            $ret[$row['id']] = $friendId;
        }
        return $ret;
    }
    
    /**
     * フレンド一覧を取得し、ユーザ情報を紐づけて返す
     * @param int $userId
     */
    public function getFriendsUserAll($userId)
    {
        $friendsIds = $this->getFriendsIds($userId);
        $users = $this->_storage->User->primaryApi($friendsIds);
        $ret = array();
        foreach($friendsIds as $friendsId => $friendId) {
            $ret[$friendsId] = $users[$friendId];
        }
        return $ret;
    }
    
    /**
     * 最終メッセージのIDを取得する
     * @param int $userId
     * @return array
     */
    public function getLastMessageIds($userId)
    {
        $friends = $this->getFriendsAll($userId);
        $ret = array();
        foreach($friends as $friendsId => $friend) {
            if($friend['last_message_id']) {
                $ret[$friendsId] = $friend['last_message_id'];
            }
        }
        return $ret;
    }
    
    /**
     * フレンズ登録処理を行う
     * 登録が成功した場合friends_idを返す
     * @param int $userId
     * @param int $friendUserId
     * @return int
     */
    public function addFriend($userId,$friendUserId)
    {
        if(!$userId||!is_int($userId)||!$friendUserId||!is_int($friendUserId)) {
            throw new \InvalidArgumentException();
        }
        $friendsId = $this->add($userId,$friendUserId);
        if(!$friendsId) {
            throw new \ErrorException();
        }
        $this->_storage->UserFriend->add($userId,$friendUserId,$friendsId);
        return $friendsId;
    }
    
    /**
     * 友だち情報を登録し、idを返す。
     * AとBが友達になる場合、
     * 小さい方のuser_idの値を用いてshardingする。
     * @param int $userId
     * @param int $friendId
     * @param int $friendsId
     * @return int
     * @throws \InvalidArgumentException
     */
    public function add($userId1,$userId2)
    {
        $this->checkInt($userId1,$userId2);
        if($userId1>$userId2) {
            $wk = $userId1;
            $userId1 = $userId2;
            $userId2 = $wk;
        }
        
        $tableName = $this->_table_name;
        $sql = 'INSERT INTO ' . $tableName . ' (user_id_1,user_id_2,create_time,update_time) VALUES (:user_id_1,:user_id_2,:create_time,:update_time)';
        $sql.= ' ON DUPLICATE KEY UPDATE create_time=VALUES(create_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id_1',$userId1,$this->_data_types['user_id_1']);
        $stmt->bindValue(':user_id_2',$userId2,$this->_data_types['user_id_2']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        $stmt->bindValue(':update_time',time(),$this->_data_types['update_time']);
        $result = $stmt->execute();
        
        $id = 0;
        if($result) {
            $id = (int)$this->lastInsertId();
        }
        return $id;
    }
    /**
     * 最終メッセージの更新を行う
     * @param int $friendsId
     * @param int $messageId
     * @return bool
     */
    public function addMessage($friendsId,$messageId)
    {
        $this->checkInt($friendsId,$messageId);
        $values = array(
            'last_message_time' => time(),
            'last_message_id' => $messageId,
            'update_time' => time(),
        );
        return (bool)$this->updatePrimaryOne($values, $friendsId);
    }
}

