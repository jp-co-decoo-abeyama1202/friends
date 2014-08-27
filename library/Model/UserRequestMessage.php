<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserRequestMessage extends ShardingModel {
    const CONDUCT_REFUSE_MESSAGE = '[自動的にフレンド申請を拒否しました]';
    const CONDUCT_CANCELL_MESSAGE = '[自動的にフレンド申請を取り消しました]';
    protected $_table_name = 'user_request_message';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id' => \PDO::PARAM_INT,
        'user_id'   => \PDO::PARAM_INT,
        'message'  => \PDO::PARAM_STR,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';

    public function getMessage($id,$userId)
    {
        $id = (int)$id;
        $userId = (int)$userId;
        if(!$userId||!$id) {
            throw new \InvalidArgumentException();
        }
        list($_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName . " WHERE id = :id";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':id',$id,$this->_data_types['id']);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function getMessages($ids,$userId)
    {
        if(!$userId||!$ids) {
            throw new \InvalidArgumentException();
        }
        list($_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName . " WHERE id IN (".implode(',',$ids).")";
        $stmt = $this->_con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * $list = array(
     *     {user_id} => {id}
     *     ...
     *     {user_id} => {id}
     * );
     * @param type $list
     */
    public function getMessagesFromRequestTo($list)
    {
        $userIds = array_keys($list);
        $tables = $this->getTableNames($userIds);
        $ret = array();
        foreach($tables as $t) {
            $tableName = $t[1];
            $userIds = $t[0];
            $ids = array();
            foreach($userIds as $userId) {
                $ids[] = $list[$userId];
            }
            $sql = 'SELECT user_id,message FROM ' . $tableName . " WHERE id IN (".implode(',',$ids).")";
            $stmt = $this->_con->prepare($sql);
            $stmt->execute();
            while($r = $stmt->fetch()) {
                $ret[(int)$r['user_id']] = $r['message'];
            }
        }
        return $ret;
    }
    
    /**
     * リクエストテキストを登録し、IDを返す
     * @param type $userId
     * @param type $message
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($userId,$message)
    {
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'INSERT INTO ' . $tableName . ' (user_id,message) VALUES (:user_id,:message)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':message',$message,$this->_data_types['message']);
        $result = $stmt->execute();
        $id = 0;
        if($result) {
            $id = $this->lastInsertId();
        }
        return $id;
    }
    
    /**
     * リクエストテキストを更新する
     * @param type $id
     * @param type $userId
     * @param type $message
     * @return type
     * @throws \InvalidArgumentException
     */
    public function update($id,$userId,$message)
    {
        $id = (int)$id;
        $userId = (int)$userId;
        if(!$userId||!$id) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'UPDATE ' . $tableName . ' SET message=:message WHERE id = :id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':message',$message,$this->_data_types['message']);
        $stmt->bindValue(':id',$id,$this->_data_types['id']);
        return $stmt->execute();
    }
}

