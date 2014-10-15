<?php

/**
 * 申請された内容
 * @author Administrafrom_idr
 */
namespace library;
class Model_UserRequestTo extends Model_UserRequest {
    
    protected $_table_name = 'user_request_to';
    protected $_primary_key = array('user_id','from_id');
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
    public static $_targetUserIdColumn = "from_id";
    
    
    
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
        
        $allCount = count($list);
        if($allCount > $offset + $count) {
            $list = array_slice($list,$offset*$count,$count);
        }
        
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
        return array($list_,$allCount);
    }
    
    public function getRequests($userId)
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
        
        $tColumn = static::$_targetUserIdColumn;
        $ret = array();
        foreach($list as $row) {
            $id = (int)$row[$tColumn];
            $ret[$id] = $row;
        }
        return $ret;
    }
}

