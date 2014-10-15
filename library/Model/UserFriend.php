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
        'push_chat'   => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';
    const DEFAULT_COUNT = 30;
    
    public function get($userId,$friendId)
    {
        $this->checkInt($userId,$friendId);
        $id = array(
            'user_id' => $userId,
            'friend_id' => $friendId,
        );
        return $this->primaryOne($id);
    }
    
    public function getFriendsIds($userId)
    {
        $this->checkInt($userId);
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT friend_id FROM ' . $tableName . ' WHERE user_id = :user_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $ids = array();
        while($id = (int)$stmt->fetchColumn()) {
            $ids[] = $id;
        }
        return $ids;
    }
    
    public function getFriends($userId,$offset=0,$count=self::DEFAULT_COUNT)
    {
        $this->checkInt($userId);
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT f.friend_id,t.token as friend_token,name,sex,age,country,area,request,publishing,profile,device,state,image,login_time FROM ' . $tableName . ' as f ';
        $sql.= ' LEFT JOIN user as u ON f.friend_id = u.id';
        $sql.= ' LEFT JOIN user_token as t ON f.friend_id = t.id';
        $sql.= ' WHERE f.user_id = :user_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $allCount = count($list);
        if($allCount > $offset + $count) {
            $list = array_slice($list,$offset*$count,$count);
        }
        
        $ret = array();
        foreach($list as $values) {
            $friendId = (int)$values['friend_id'];
            $ret[$friendId] = $values;
        }
        return array($ret,$allCount);
    }
    
    public function getFriendsAll($userId)
    {
        $this->checkInt($userId);
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
        $this->checkInt($userId,$friendId);
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
        $this->checkInt($userId,$friendId,$friendsId);
        if($userId === $friendId) {
            throw new \InvalidArgumentException();
        }
        $keys = array(
            'user_id','friend_id','friends_id','create_time','update_time'
        );
        $userParams = array(
            'user_id' => $userId,
            'friend_id' => $friendId,
            'friends_id' => $friendsId,
            'create_time' => time(),
            'update_time' => time(),
        );
        $friendParams = array(
            'user_id' => $friendId,
            'friend_id' => $userId,
            'friends_id' => $friendsId,
            'create_time' => time(),
            'update_time' => time(),
        );
        $this->insertOne($userId, $userParams);
        $this->insertOne($friendId, $friendParams);
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
        $this->checkInt($userId,$friendId);
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

