<?php

/**
 * Description of Test
 *
 * @author Administrato_idr
 */
namespace library;
class Model_UserBlock extends ShardingModel {  
    const MODE_ADD = "add";
    const MODE_DEL = "delete";
    
    public static $modeList = array(
        self::MODE_ADD,
        self::MODE_DEL,
    );
    
    protected $_table_name = 'user_block';
    protected $_primary_key = array('user_id','block_id');
    protected $_data_types = array(
        'user_id'   => \PDO::PARAM_INT,
        'block_id'  => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';

    /**
     * 
     * @param type $userId 
     * @param type $blockId
     * @param bool $dualCheck どちらか片方がブロックしているかチェック
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function check($userId,$blockId,$dualCheck=false)
    {
        $userId = (int)$userId;
        $blockId = (int)$blockId;
        if(!$userId||!$blockId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT 1 FROM ' . $tableName . ' WHERE user_id = :user_id AND block_id = :block_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':block_id',$blockId,$this->_data_types['block_id']);
        $stmt->execute();
        $ret = (bool)$stmt->fetchColumn();
        if($dualCheck) {
            $ret = $ret || $this->check($blockId,$userId,false);
        }
        return $ret;
    }
    
    public function getBlockList($userId)
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
    
    public function getBlockIds($userId)
    {
        return array_keys($this->getBlocks($userId));
    }
    
    public function getBlocks($userId) 
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
        foreach($list as $row) {
            $id = (int)$row['block_id'];
            $ret[$id] = $row;
        }
        return $ret;
    }
    
    /**
     * ブロック情報を登録する。
     * @param type $userId
     * @param type $blockId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($userId,$blockId)
    {
        if(!$userId||!$blockId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'INSERT INTO ' . $tableName . ' (user_id,block_id,create_time) VALUES (:user_id,:block_id,:create_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':block_id',$blockId,$this->_data_types['block_id']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        return $stmt->execute();
    }
    
    /**
     * ブロック情報を削除する。
     * @param type $userId
     * @param type $blockId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function delete($userId,$blockId)
    {
        if(!$userId||!$blockId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'DELETE FROM ' . $tableName . ' WHERE user_id = :user_id AND block_id = :block_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':block_id',$blockId,$this->_data_types['block_id']);
        return $stmt->execute();
    }
}

