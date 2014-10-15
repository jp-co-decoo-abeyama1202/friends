<?php

/**
 * Description of Test
 *
 * @author Administrato_idr
 */
namespace library;
class Model_UserRequestFrom extends Model_UserRequest {
    
    protected $_table_name = 'user_request_from';
    protected $_primary_key = array('user_id','to_id');
    protected $_data_types = array(
        'user_id'   => \PDO::PARAM_INT,
        'to_id'  => \PDO::PARAM_INT,
        'message_id'  => \PDO::PARAM_INT,
        'state'       => \PDO::PARAM_INT,
        'delete_flag'  => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';
    public static $_targetUserIdColumn = 'to_id';
    
    /**
     * フレンドタブ表示用情報を表示する
     * @param int $userId
     * @param int $offset
     * @param int $count
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getList($userId,$offset=0,$count=self::DEFAULT_COUNT)
    {
        if(!$userId||!is_int($userId)||$offset<0||$count<0) {
            throw new \InvalidArgumentException();
        }
        $tColumn = static::$_targetUserIdColumn;
        list($i_,$tableName) = $this->getTableName($userId);
        list($m_,$mTableName) = $this->_storage->UserRequestMessage->getTableName($userId);
        $sql = "SELECT $tColumn,message,state,create_time,update_time FROM " . $tableName ;
        $sql.= ' LEFT JOIN ' . $mTableName . ' ON '.$tableName.'.message_id = ' . $mTableName . '.id';
        $sql.= ' WHERE '.$tableName.'.user_id = :user_id AND (state = '.self::STATE_PENDING.' OR ( state = '.self::STATE_REFUSE.' AND delete_flag = '.self::DELETE_OFF.')) ORDER BY create_time DESC';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        //全件数
        $allCount = count($list);
        if($allCount > $offset + $count) {
            $list = array_slice($list,$offset*$count,$count);
        }
        
        $userIds = array();
        $list_ = array();
        
        foreach($list as $l) {
            $userIds[] = (int)$l['to_id'];
        }
        //リクエストリスト用データを取得
        $users = $this->_storage->User->getDataFromRequestList($userIds);
        
        foreach($list as $l) {
            $userId = (int)$l['to_id'];
            $l['user'] = $users[$userId];
            $l['to_id'] = $users[$userId]['id'];
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
        
        $ret = array();
        foreach($list as $row) {
            $id = (int)$row['to_id'];
            $ret[$id] = $row;
            
        }
        return $ret;
    }
}

