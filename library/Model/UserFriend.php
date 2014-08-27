<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserFriend extends ShardingModel {
    protected $_table_name = 'user_friend';
    protected $_primary_key = array('user_id','friend_id');
    protected $_data_types = array(
        'user_id'   => \PDO::PARAM_INT,
        'friend_id'  => \PDO::PARAM_INT,
        'friends_id'  => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';
    
    public function getFriendsIds($userId)
    {
        return array_keys($this->getFriends($userId));
    }
    
    public function getFriends($userId)
    {
        $userId = (int)$userId;
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE user_id = :user_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $ret = array();
        foreach($list as $values) {
            $friendId = (int)$values['friend_id'];
            $ret[$friendId] = $values;
        }
        return $ret;
    }
    
    /**
     * 既にフレンドかチェック
     * @param type $userId
     * @param type $friendId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function checkFriend($userId,$friendId)
    {
        $userId = (int)$userId;
        $friendId = (int)$friendId;
        if(!$userId||!$friendId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT 1 FROM ' . $tableName . ' WHERE user_id = :user_id AND friend_id = :friend_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':friend_id',$friendId,$this->_data_types['friend_id']);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }
    
    /**
     * 友だち情報を登録する。
     * AとBが友達になる場合、A用のテーブル friend_XXと
     * B用のテーブル friend_YY の両方に登録を行う
     * @param type $userId
     * @param type $friendId
     * @param type $friendsId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($userId,$friendId,$friendsId)
    {
        $userId = (int)$userId;
        $friendId = (int)$friendId;
        $friendsId = (int)$friendsId;
        if(!$userId||!$friendId||!$friendsId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'INSERT INTO ' . $tableName . ' (user_id,friend_id,friends_id,create_time) VALUES (:user_id,:friend_id,:friends_id,:create_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':friend_id',$friendId,$this->_data_types['friend_id']);
        $stmt->bindValue(':friends_id',$friendsId,$this->_data_types['friends_id']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        $r1 = $stmt->execute();
        
        //逆パターンも登録
        list($ids,$tableName) = $this->getTableName($friendId);
        $sql = 'INSERT INTO ' . $tableName . ' (user_id,friend_id,friends_id,create_time) VALUES (:user_id,:friend_id,:friends_id,:create_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$friendId,$this->_data_types['user_id']);
        $stmt->bindValue(':friend_id',$userId,$this->_data_types['friend_id']);
        $stmt->bindValue(':friends_id',$friendsId,$this->_data_types['friends_id']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        $r2 = $stmt->execute();
        return (bool)$r1 && (bool)$r2;
    }
    
    /**
     * 友だち情報を削除する。
     * AとBが友達になる場合、A用のテーブル friend_XXと
     * B用のテーブル friend_YY の両方から削除を行う
     * @param type $userId
     * @param type $friendId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function delete($userId,$friendId)
    {
        $userId = (int)$userId;
        $friendId = (int)$friendId;
        if(!$userId||!$friendId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'DELETE FROM ' . $tableName . ' WHERE user_id = :user_id AND friend_id = :friend_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':friend_id',$friendId,$this->_data_types['friend_id']);
        $r1 = $stmt->execute();
        
        //逆パターンも削除
        list($ids,$tableName) = $this->getTableName($friendId);
        $sql = 'DELETE FROM ' . $tableName . ' WHERE user_id = :user_id AND friend_id = :friend_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$friendId,$this->_data_types['user_id']);
        $stmt->bindValue(':friend_id',$userId,$this->_data_types['friend_id']);
        $r2 = $stmt->execute();
        return (bool)$r1 && (bool)$r2;
    }
}

