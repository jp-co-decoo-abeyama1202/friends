<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserGroupLog extends ShardingModel {
    protected $_table_name = 'user_group_log';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id' => \PDO::PARAM_INT,
        'user_id' => \PDO::PARAM_INT,
        'group_id' => \PDO::PARAM_INT,
        'invitation_id' => \PDO::PARAM_INT,
        'state' => \PDO::PARAM_INT,
        'deleter_id' => \PDO::PARAM_INT,
        'invitation_time' => \PDO::PARAM_INT,
        'add_group_time' => \PDO::PARAM_INT,
        'leave_group_time' => \PDO::PARAM_INT,
        'last_message_id' => \PDO::PARAM_INT,
        'update_time' => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';
    
    public function primary($ids) {
        throw new \BadFunctionCallException();
    }
    
    public function get($id,$userId)
    {
        if(!$userId||!is_int($userId)||!$id||!is_int($id)) {
            throw new \InvalidArgumentException();
        }
        list($_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE id = :id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':id',$id,self::$_data_types['id']);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function gets($userId,$groupId)
    {
        if(!$userId||!is_int($userId)||!$groupId||!is_int($groupId)) {
            throw new \InvalidArgumentException();
        }
        list($_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE user_id = :user_id AND group_id = :group_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(':group_id',$groupId,$this->_data_types['group_id']);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function add($userId,$groupId,$invitationId)
    {
        if(!$userId||!is_int($userId)||!$groupId||!is_int($groupId)||!$invitationId||!is_int($invitationId)) {
            throw new \InvalidArgumentException();
        }
        $param = array(
            'user_id' => $userId,
            'group_id' => $groupId,
            'invitation_id' => $invitationId,
            'state' => $userId === $invitationId ? Model_UserGroup::STATE_SUBMIT : Model_UserGroup::STATE_INVITATE,
            'invitation_time' => time(),
            'add_group_time' => $userId === $invitationId ? time() : 0,
            'update_time' => time(),
        );
        $result = $this->insertOne($userId, $param);
        $logId = 0;
        if($result) {
            $logId = $this->lastInsertId();
        }
        return $logId;
    }
    
    /**
     * ステータス変化
     * @param int $id
     * @param int $userId
     * @param int $state
     * @throws \InvalidArgumentException
     */
    public function changeState($id,$userId,$state,$deleterId=null,$lastMessageId=null) 
    {
        if(!$id||!is_int($id)||!$userId||!is_int($userId)||!in_array($state,array(Model_UserGroup::STATE_SUBMIT,Model_UserGroup::STATE_DELETE,Model_UserGroup::STATE_REFUSE,Model_UserGroup::STATE_CANCEL))) {
            throw new \InvalidArgumentException();
        }
        if(!is_null($deleterId) && !is_int($deleterId) && !$deleterId) {
            throw new \InvalidArgumentException();
        }
        if(!is_null($lastMessageId) && !is_int($lastMessageId) && !$lastMessageId) {
            throw new \InvalidArgumentException();
        }
        $values = array(
            'state' => $state,
            'update_time' => time(),
        );
        if($state === Model_UserGroup::STATE_SUBMIT) {
            $values['add_group_time'] = time();
        }
        if($state === Model_UserGroup::STATE_DELETE) {
            if(!$deleterId||!$lastMessageId) {
                throw new \InvalidArgumentException();
            }
            $values['deleter_id'] = $deleterId;
            $values['last_message_id'] = $lastMessageId;
            $values['leave_group_time'] = time();
        }
        $this->updatePrimaryOne($values, $id, $userId);
    }
        
    public function checkAddGroupHistory($userId,$groupId)
    {
        $this->checkInt($userId,$groupId);
        $list = $this->gets($userId,$groupId);
        foreach($list as $row) {
            if((int)$row['add_group_time'] > 0) {
                return true;
            }
        }
        return false;
    }
    
    public function getAddGroupTimes($userId,$groupId)
    {
        $this->checkInt($userId,$groupId);
        $list = $this->gets($userId,$groupId);
        $ret = array();
        foreach($list as $row) {
            if((int)$row['add_group_time'] === 0) {
                continue;
            }
            $ret[] = array(
                (int)$row['add_group_time'],
                (int)$row['leave_group_time']
            );
        }
        return $ret;
    }
}

