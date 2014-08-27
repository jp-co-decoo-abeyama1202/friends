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
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'user_id_1'  => \PDO::PARAM_INT,
        'user_id_2'  => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
    );
    
    public function getId($userId1,$userId2)
    {
        $userId1 = (int)$userId1;
        $userId2 = (int)$userId2;
        if(!$userId1||!$userId2) {
            throw new \InvalidArgumentException();
        }
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
    
    /**
     * 友だち情報を登録し、idを返す。
     * AとBが友達になる場合、
     * 小さい方のuser_idの値を用いてshardingする。
     * @param type $userId
     * @param type $friendId
     * @param type $friendsId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($userId1,$userId2)
    {
        $userId1 = (int)$userId1;
        $userId2 = (int)$userId2;
        if(!$userId1||!$userId2) {
            throw new \InvalidArgumentException();
        }
        if($userId1>$userId2) {
            $wk = $userId1;
            $userId1 = $userId2;
            $userId2 = $wk;
        }
        
        $tableName = $this->_table_name;
        $sql = 'INSERT INTO ' . $tableName . ' (user_id_1,user_id_2,create_time) VALUES (:user_id_1,:user_id_2,:create_time)';
        $sql.= ' ON DUPLICATE KEY UPDATE create_time=VALUES(create_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id_1',$userId1,$this->_data_types['user_id_1']);
        $stmt->bindValue(':user_id_2',$userId2,$this->_data_types['user_id_2']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        $result = $stmt->execute();
        
        $id = 0;
        if($result) {
            $id = $this->lastInsertId();
        }
        return $id;
    }
}

