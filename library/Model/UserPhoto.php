<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserPhoto extends ShardingModel {
    const MAX_COUNT = 8;
    protected $_table_name = 'user_photo';
    protected $_primary_key = array(
        'user_id','no'
    );
    protected $_data_types = array(
        'user_id' => \PDO::PARAM_INT,
        'no' => \PDO::PARAM_INT,
        'image' => \PDO::PARAM_LOB,
        'create_time' => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';
    
    /**
     * 指定されたユーザが何枚の画像を登録済みか
     * @param int $userId
     */
    public function getUserPhotoCount($userId)
    {
        $userId = (int)$userId;
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = "SELECT count(*) FROM " . $tableName . ' WHERE user_id = :user_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    /**
     * 次のnoを取得する
     * @param int $userId
     * @return type
     */
    public function getNextPhotoNo($userId)
    {
        $userId = (int)$userId;
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = "SELECT max(no) FROM " . $tableName . " WHERE user_id = :user_id";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId);
        $stmt->execute();
        return (int)$stmt->fetchColumn() + 1;
    }
    
    /**
     * Photo一覧を取得する
     * @param int $userId
     * @return type
     */
    public function getPhotoList($userId)
    {
        $userId = (int)$userId;
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = "SELECT no,image FROM " . $tableName . " WHERE user_id = :user_id ORDER BY no ASC";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $i = 1;
        $ret = array();
        foreach($list as $values) {
            $ret['photo_'.$i] = array(
                'no' => $values['no'],
                'image' => $values['image'],
            );
            $i++;
        }
        return $ret;
    }
    
    /**
     * 写真データを登録する
     * @param int $userId
     * @param binary $image
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function add($userId,$image)
    {
        if(!$userId||!$image) {
            throw new \InvalidArgumentException();
        }
        $no = $this->getNextPhotoNo($userId);
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = "INSERT INTO " . $tableName . " (user_id,no,image,create_time) VALUES (:user_id,:no,:image,:create_time)";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':no',$no,$this->_data_types['no']);
        $stmt->bindValue(':image',$image,$this->_data_types['image']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        return (bool)$stmt->execute();
    }
    
    public function delete($userId,$no)
    {
        if(!$userId||!$no) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = "DELETE FROM " . $tableName . " WHERE user_id=:user_id AND no=:no";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':no',$no,$this->_data_types['no']);
        return (bool)$stmt->execute();
    }
}

