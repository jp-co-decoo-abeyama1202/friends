<?php

/**
 * ユーザ毎の所属グループ
 *
 * @author Administrator
 */
namespace library;
class Model_UserGroup extends ShardingModel 
{
    /**
     * 招待された
     */
    const STATE_INVITATE = 0;
    /**
     * 承認した
     */
    const STATE_SUBMIT = 1;
    /**
     * 拒否した
     */
    const STATE_REFUSE = 2;
    /**
     * 削除した・された
     */
    const STATE_DELETE = 3;
    
    /**
     *
     * キャンセル
     */
    const STATE_CANCEL = 4;
    
    public static $_stateList =array(
        self::STATE_INVITATE => '招待中',
        self::STATE_SUBMIT => '承認',
        self::STATE_REFUSE => '拒否',
        self::STATE_DELETE => '退出済み',
        self::STATE_CANCEL => 'キャンセル',
    );
    
    protected $_table_name = 'user_group';
    protected $_primary_key = array('user_id','group_id');
    protected $_data_types = array(
        'user_id'   => \PDO::PARAM_INT,
        'group_id'  => \PDO::PARAM_INT,
        'state' => \PDO::PARAM_INT,
        'user_group_log_id' => \PDO::PARAM_INT,
        'last_message_id' => \PDO::PARAM_INT,
        'push_chat' => \PDO::PARAM_INT,
        'create_time' => \PDO::PARAM_INT,
        'update_time' => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';
    const DEFAULT_COUNT = 30;
    
    public function get($userId,$groupId)
    {
        if(!$userId||!is_int($userId)||!$groupId||!is_int($groupId)) {
            throw new \InvalidArgumentException();
        }
        $id = array(
            'user_id' => $userId,
            'group_id' => $groupId,
        );
        return $this->primaryOne($id);
    }
    
    public function getGroupIds($userId)
    {
        if(!$userId||!is_int($userId)) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT group_id FROM ' . $tableName . ' WHERE user_id = :user_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $ids = array();
        while($id = (int)$stmt->fetchColumn()) {
            $ids[] = $id;
        }
        return $ids;
    }
    
    public function getGroups($userId,$offset=0,$count=self::DEFAULT_COUNT)
    {
        if(!$userId||!is_int($userId)||$offset<0||$count<0) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT u.group_id,u.create_time as join_time,g.name,g.image,g.delete_flag FROM ' . $tableName . ' as u ';
        $sql.= ' LEFT JOIN groups as g ON u.group_id = g.id';
        $sql.= ' WHERE u.user_id = :user_id';
        $sql.= ' AND u.state NOT IN (' . implode(",",array(Model_UserGroup::STATE_REFUSE,Model_UserGroup::STATE_DELETE,Model_UserGroup::STATE_CANCEL)) .') ';
        $sql.= ' AND g.delete_flag = ' . Model_Group::DELETE_OFF;
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
            $groupId = (int)$values['group_id'];
            $ret[$groupId] = $values;
        }
        return array($ret,$allCount);
    }
    
    public function getGroupAll($userId)
    {
        if(!$userId||!is_int($userId)) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName . ' WHERE user_id = :user_id';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        $ret = array();
        foreach($list as $values) {
            $groupId = (int)$values['group_id'];
            $ret[$groupId] = $values;
        }
        return $ret;
    }
    
    /**
     * グループ情報を登録する。
     * @param int $userId
     * @param int $groupId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($userId,$groupId,$invitationId)
    {
        if(!$userId||!is_int($userId)||!$groupId||!is_int($groupId)||!$invitationId||!is_int($invitationId)) {
            throw new \InvalidArgumentException();
        }
        $this->checkInt($userId,$groupId,$invitationId);
        //ログに登録
        $logId = $this->_storage->UserGroupLog->add($userId,$groupId,$invitationId);
        if(!$logId) {
            throw new \ErrorException();
        }
        $state = $this->_storage->GroupUser->checkGroupAtUserId($groupId,$userId,false);
        
        if(is_null($state)) {
            $params = array(
                'user_id' => $userId,
                'group_id' => $groupId,
                'state' => $userId === $invitationId ? self::STATE_SUBMIT : self::STATE_INVITATE,
                'user_group_log_id' => $logId,
                'create_time' => time(),
                'update_time' => time(),
            );
            return $this->insertOne($userId,$params);
        } else {
            if(in_array($state,array(self::STATE_SUBMIT,self::STATE_INVITATE))) {
                throw new \InvalidArgumentException();
            }
            $params = array(
                'state' => self::STATE_INVITATE,
                'user_group_log_id' => $logId,
                'create_time' => time(),
                'update_time' => time(),
            );
            $primaryKeys = array(
                'group_id' => $groupId,
                'user_id' => $userId,
            );
            return $this->updatePrimaryOne($params, $primaryKeys, $userId);
        }
    }
    
    /**
     * グループ情報を登録する。
     * @param array $userIds
     * @param int $groupId
     */
    public function adds($userIds,$groupId,$invitationId)
    {
        foreach($userIds as $userId) {
            $this->add($userId,$groupId,$invitationId);
        }
    }
    
    /**
     * ステータスの変更
     * @param type $userId
     * @param type $groupId
     * @param type $state
     */
    public function changeState($userId,$groupId,$state)
    {
        if(!$groupId||!is_int($groupId)||!$userId||!is_int($userId)||!in_array($state,array(self::STATE_SUBMIT,self::STATE_REFUSE,self::STATE_CANCEL))) {
            throw new \InvalidArgumentException();
        }
        $userGroup = $this->get($userId,$groupId);
        if(!$userGroup) {
            throw new \ErrorException();
        }
        $logId = (int)$userGroup['user_group_log_id'];
        $this->_storage->UserGroupLog->changeState($logId,$userId,$state);
        $values = array(
            'state' => $state,
            'update_time' => time(),
        );
        if($state === self::STATE_SUBMIT) {
            $values['last_message_id'] = 0;
        }
        $id = array(
            'user_id' => $userId,
            'group_id' => $groupId,
        );
        return $this->updatePrimaryOne($values, $id, $userId);
    }
    
    /**
     * グループを抜ける
     * 論理削除を行う。
     * @param int $userId
     * @param int $groupId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function delete($userId,$groupId,$deleterId)
    {
        $this->checkInt($userId,$groupId,$deleterId);
        $users = $this->_storage->User->primary(array($userId,$deleterId));
        $user = $users[$userId];
        $deleter = $users[$deleterId];
        $userGroup = $this->get($userId,$groupId);
        if(!$userGroup||$userGroup['state'] != self::STATE_SUBMIT) {
            throw new \ErrorException();
        }
        $logId = (int)$userGroup['user_group_log_id'];
        $messageType = $userId === $deleterId ? Model_GroupMessage::ADMIN_DELETE_SELF : Model_GroupMessage::ADMIN_DELETE_OTHER;
        $lastMessageId = (Int)$this->_storage->GroupMessage->addAdminMessage($groupId,$messageType,$name=$deleter['name'],$target=$user['name']);
        $this->_storage->UserGroupLog->changeState($logId,$userId,self::STATE_DELETE,$deleterId,$lastMessageId);
        
        $values = array(
            'state' => self::STATE_DELETE,
            'last_message_id' => $lastMessageId,
            'update_time' => time(),
        );
        $primaryKey = array(
            'user_id' => $userId,
            'group_id' => $groupId,

        );
        return $this->updatePrimaryOne($values, $primaryKey, $userId);
    }
}

