<?php

/**
 * Description of Test
 *
 * @author Administrato_idr
 */
namespace library;
class Model_UserBlocker extends ShardingModel {
    protected $_table_name = 'user_blocker';
    protected $_primary_key = array('user_id','blocker_id');
    protected $_data_types = array(
        'user_id'   => \PDO::PARAM_INT,
        'blocker_id'  => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';

    /**
     * @param type $userId 
     * @param type $blockerId
     * @param bool $dualCheck どちらか片方がブロックしているかチェック
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function check($userId,$blockerId,$dualCheck=false)
    {
        $userId = (int)$userId;
        $blockerId = (int)$blockerId;
        if(!$userId||!$blockerId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT 1 FROM ' . $tableName . ' WHERE user_id = :user_id AND blocker_id = :blocker_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':blocker_id',$blockerId,$this->_data_types['blocker_id']);
        $stmt->execute();
        $ret = (bool)$stmt->fetchColumn();
        if($dualCheck) {
            $ret = $ret || $this->check($blockerId,$userId,false);
        }
        return $ret;
    }
    
    public function getBlockerList($userId)
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
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function getBlockerIds($userId)
    {
        return array_keys($this->getBlockers($userId));
    }
    
    public function getBlockers($userId) 
    {
        $userId = (int)$userId;
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        list($_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE user_id = :user_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $ret = array();
        foreach($list as $row) {
            $id = (int)$row['blocker_id'];
            $ret[$id] = $row;
        }
        return $ret;
    }
    
    /**
     * ブロッカー情報を登録する。
     * @param type $userId
     * @param type $blockerId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($userId,$blockerId)
    {
        if(!$userId||!$blockerId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'INSERT INTO ' . $tableName . ' (user_id,blocker_id,create_time) VALUES (:user_id,:blocker_id,:create_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':blocker_id',$blockerId,$this->_data_types['blocker_id']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        return $stmt->execute();
    }
    
    /**
     * ブロッカー情報を削除する。
     * @param type $userId
     * @param type $blockerId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function delete($userId,$blockerId)
    {
        if(!$userId||!$blockerId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'DELETE FROM ' . $tableName . ' WHERE user_id = :user_id AND blocker_id = :blocker_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':blocker_id',$blockerId,$this->_data_types['blocker_id']);
        return $stmt->execute();
    }
}

