<?php

/**
 * Description of Test
 *
 * @author Administrafrom_idr
 */
namespace library;
class Model_UserRequestTo extends Model_UserRequestFrom {
    
    protected $_table_name = 'user_request_to';
    protected $_primary_key = array('user_id','tho_id');
    protected $_data_types = array(
        'user_id'   => \PDO::PARAM_INT,
        'from_id'  => \PDO::PARAM_INT,
        'message_id'  => \PDO::PARAM_INT,
        'state'       => \PDO::PARAM_INT,
        'delete_flag'  => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';

    
    public function get($userId,$from_id)
    {
        $userId = (int)$userId;
        $from_id = (int)$from_id;
        if(!$userId||!$from_id) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE user_id = :user_id AND from_id = :from_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':from_id',$from_id,$this->_data_types['from_id']);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
/**
     * 受信状況一覧を取得する
     * @param type $userId
     * @throws \InvalidArgumentException
     */
    public function getList($userId,$offset=0,$count=self::DEFAULT_COUNT)
    {
        $userId = (int)$userId;
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        list($i_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT from_id,message_id,state,create_time,update_time FROM ' . $tableName ;
        $sql.= ' WHERE '.$tableName.'.user_id = :user_id AND state = '.self::STATE_PENDING.' ORDER BY create_time DESC';
        $sql.= ' LIMIT ' . $offset . "," .$count;
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $userIds = array();
        $messageList = array();
        $list_ = array();
        
        foreach($list as $l) {
            $userIds[] = (int)$l['from_id'];
            $messageList[(int)$l['from_id']] = (int)$l['message_id'];
        }
        
        $users = $this->_storage->User->getDataFromRequestList($userIds);
        $messages = $this->_storage->UserRequestMessage->getMessagesFromRequestTo($messageList);
        foreach($list as $l) {
            $userId = (int)$l['from_id'];
            $l['user'] = $users[$userId];
            $l['from_id'] = $users[$userId]['id'];
            $l['message'] = $messages[$userId];
            unset($l['message_id']);
            $list_[] = $l;
        }
        return $list_;
    }
    
    public function getRequestTos($userId)
    {
        $userId = (int)$userId;
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        list($i_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName ;
        $sql.= ' WHERE '.$tableName.'.user_id = :user_id ';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $ret = array();
        foreach($list as $row) {
            $id = (int)$row['from_id'];
            $ret[$id] = $row;
        }
        return $ret;
    }
    
    public function getRequestToIds($userId)
    {
        return array_keys($this->getRequestTos($userId));
    }
    
    /**
     * リクエストを登録する。
     * ON DUPLICATE KEYでやる
     * @param type $userId
     * @param type $from_id
     * @param type $messageId
     * @param type $state
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($userId,$from_id,$messageId,$state,$deleteFlag=self::DELETE_OFF)
    {
        if(!$userId||!$messageId||!$from_id) {
            throw new \InvalidArgumentException();
        }
        if(!in_array($deleteFlag,array(self::DELETE_OFF,self::DELETE_ON))) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'INSERT INTO ' . $tableName . ' (user_id,from_id,message_id,state,create_time,update_time,delete_flag) VALUES (:user_id,:from_id,:message_id,:state,:create_time,:update_time,:delete_flag)';
        $sql.= ' ON DUPLICATE KEY UPDATE state=VALUES(state),delete_flag=VALUES(delete_flag),update_time=VALUES(update_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':from_id',$from_id,$this->_data_types['from_id']);
        $stmt->bindValue(':message_id',$messageId,$this->_data_types['message_id']);
        $stmt->bindValue(':state',$state,$this->_data_types['state']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        $stmt->bindValue(':update_time',time(),$this->_data_types['update_time']);
        $stmt->bindValue(':delete_flag',$deleteFlag,$this->_data_types['delete_flag']);
        return $stmt->execute();
    }
    
    public function update($userId,$from_id,$state)
    {
        if(!$userId||!$from_id) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'UPDATE ' . $tableName . ' SET state = :state, update_time = :update_time WHERE user_id = :user_id AND from_id = :from_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':from_id',$from_id,$this->_data_types['from_id']);
        $stmt->bindValue(':state',$state,$this->_data_types['state']);
        $stmt->bindValue(':update_time',time(),$this->_data_types['update_time']);
        return $stmt->execute();
    }
    
    /**
     * 申請情報を削除する(論理削除)
     * @param type $userId
     * @param type $friendId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function delete($userId,$from_id)
    {
        $userId = (int)$userId;
        $from_id = (int)$from_id;
        if(!$userId||!$from_id) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'UPDATE ' . $tableName . ' SET delete_flag = :delete_flag WHERE user_id = :user_id AND from_id = :from_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':from_id',$from_id,$this->_data_types['from_id']);
        $stmt->bindValue(':delete_flag',self::DELETE_ON,$this->_data_types['delete_flag']);
        return $stmt->execute();
    }
}

