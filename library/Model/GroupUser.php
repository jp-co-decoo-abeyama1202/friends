<?php

/**
 * グループごとの所属ユーザ
 *
 * @author Administrator
 */
namespace library;
class Model_GroupUser extends ShardingModel {
    const MAX_USER = 1000;
    
    protected $_table_name = 'group_user';
    protected $_primary_key = array('group_id','user_id');
    protected $_data_types = array(
        'group_id'  => \PDO::PARAM_INT,
        'user_id'  => \PDO::PARAM_INT,
        'invitation_id' => \PDO::PARAM_INT,
        'state' => \PDO::PARAM_INT,
        'deleter_id' => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
        'update_time' => \PDO::PARAM_INT,
    );
    protected $_sharding = 50;
    protected $_sharding_key = 'group_id';
    protected $_groupUsers = array();
    protected $_checkGroupUserIds = array();
    
    /**
     * グループに入れる
     * @param int $groupId
     * @param int $userId
     * @param int $invitationId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function add($groupId,$userId,$invitationId)
    {
        return $this->adds($groupId,array($userId),$invitationId);
    }
    
    /**
     * グループへユーザを追加する
     * @param int $groupId
     * @param array $userIds
     * @param int $invitationId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function adds($groupId,$userIds,$invitationId)
    {
        if(!is_int($groupId)||!$groupId||!is_int($invitationId)||!$invitationId) {
            throw new \InvalidArgumentException();
        }
        $keys = array('group_id','user_id','invitation_id','state','deleter_id','create_time','update_time');
        $inserts = array(
            $groupId => array(),
        );
        $insertOn = false;
        $updateParams = array(
            'invitation_id' => $invitationId,
            'deleter_id' => 0,
            'state' => Model_UserGroup::STATE_INVITATE,
            'create_time' => time(),
            'update_time' => time(),
        );
        $updateOn = false;
        $updates = array(
            $groupId => array(),
        );
        $checks = $this->checkGroupAtUserIds($groupId, $userIds,false);
        foreach($userIds as $id) {
            if(!is_int($id)||!$id) {
                throw new \InvalidArgumentException();
            }
            if(array_key_exists($id,$checks)) {
                if(in_array($checks[$id],array(Model_UserGroup::STATE_SUBMIT,Model_UserGroup::STATE_INVITATE))) {
                    //登録済み or 申請待ち
                    throw new InvalidArgumentException();
                }
                $updates[$groupId][] = array(
                    'group_id' => $groupId,
                    'user_id' => $id,
                );
                $updateOn = true;
            } else {
                $inserts[$groupId][] = array(
                    'group_id' => $groupId,
                    'user_id' => $id,
                    'state' => $id == $invitationId ? Model_UserGroup::STATE_SUBMIT : Model_UserGroup::STATE_INVITATE,
                    'invitation_id' => $invitationId,
                    'deleter_id' => 0,
                    'create_time' => time(),
                    'update_time' => time(),
                );
                $insertOn = true;
            }
        }
        if($updateOn) {
            $this->updatePrimary($updateParams, $updates);
        }
        if($insertOn) {
            $this->insert($keys,$inserts);
        }
        return true;
    }
    
    /**
     * 
     * @param type $groupId
     * @param type $userIds
     * @param type $state
     * @throws \InvalidArgumentException
     */
    public function changeState($groupId,$userIds,$state)
    {
        if(!is_int($groupId)||!$groupId||!$userIds||!in_array($state,array(Model_UserGroup::STATE_REFUSE,Model_UserGroup::STATE_SUBMIT,Model_UserGroup::STATE_CANCEL))) {
            throw new \InvalidArgumentException();
        }
        $ids = array();
        foreach($userIds as $userId) {
            if(!$userId||!is_int($userId)) {
                throw new \InvalidArgumentException();
            }
            $primaryKey = array(
                'group_id' => $groupId,
                'user_id' => $userId,
            );
            $ids[] = $primaryKey;
        }
        if(!$ids) {
            throw new \InvalidArgumentException();
        }
        $allIds = array();
        foreach($ids as $id) {
            $allIds[$groupId][] = $id;
        }
        $values = array(
            'state' => $state,
            'update_time' => time(),
        );
        return $this->updatePrimary($values, $allIds);
    }
    
    
    /**
     * グループから削除
     * 論理削除を行う。
     * @param int $groupId
     * @param array $userIds
     * @return type
     * @throws \InvalidArgumentException
     */
    public function deletes($groupId,$userIds,$deleterId)
    {
        if(!$groupId||!is_int($groupId)||!$deleterId||!is_int($deleterId)) {
            throw new \InvalidArgumentException();
        }
        $ids = array();
        foreach($userIds as $userId) {
            if(!$userId||!is_int($userId)) {
                throw new \InvalidArgumentException();
            }
            $primaryKey = array(
                'group_id' => $groupId,
                'user_id' => $userId,
            );
            $ids[] = $primaryKey;
        }
        if(!$ids) {
            throw new \InvalidArgumentException();
        }
        $allIds = array();
        foreach($ids as $id) {
            $allIds[$groupId][] = $id;
        }
        $values = array(
            'state' => Model_UserGroup::STATE_DELETE,
            'deleter_id' => $deleterId,
            'update_time' => time(),
        );
        return $this->updatePrimary($values, $allIds);
    }
    
    public function getMemberCount($groupId)
    {
        return count($this->getAll($groupId)) + count($this->getAll($groupId,Model_UserGroup::STATE_INVITATE));
    }
    
    public function getAllIds($groupId,$state=Model_userGroup::STATE_SUBMIT)
    {
        $this->checkInt($groupId);
        if(!is_int($state)||!in_array($state,array_keys(Model_UserGroup::$_stateList))) {
            throw new \InvalidArgumentException();
        }
        if(isset($this->_groupUserIds[$groupId])) {
            return $this->_groupUserIds[$groupId][$state];
        }
        $this->_groupUsers[$groupId] = array(
            Model_UserGroup::STATE_INVITATE => array(),
            Model_UserGroup::STATE_SUBMIT => array(),
            Model_UserGroup::STATE_REFUSE => array(),
            Model_UserGroup::STATE_DELETE => array(),
        );
        list($_,$tableName) = $this->getTableName($groupId);
        $sql = 'SELECT user_id,state FROM ' .$tableName;
        $sql.= ' WHERE group_id = :group_id ';
        $sql.= ' ORDER BY create_time';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(":group_id",$groupId,$this->_data_types['group_id']);
        $stmt->execute();
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $thisState = (int)$row['state'];
            if($thisState === Model_UserGroup::STATE_CANCEL) {
                continue;
            }
            $id = (int)$row['user_id'];
            $this->_groupUserIds[$groupId][$thisState][] = $id;
        }
        return $this->_groupUserIds[$groupId][$state];
    }
    
    /**
     * グループ参加済みユーザのID一覧
     * @param type $groupId
     * @param type $time
     */
    public function getMemberIds($groupId)
    {
        return $this->getAllIds($groupId,Model_UserGroup::STATE_SUBMIT);
    }
    
    /**
     * $groupIdのグループに入っているユーザ情報を全取得
     * @param int $groupId
     * @return array()
     * @throws \InvalidArgumentException
     */
    public function getAll($groupId,$state=Model_UserGroup::STATE_SUBMIT)
    {
        $this->checkInt($groupId);
        if(!is_int($state)||!in_array($state,array_keys(Model_UserGroup::$_stateList))) {
            throw new \InvalidArgumentException();
        }
        if(isset($this->_groupUsers[$groupId])) {
            return $this->_groupUsers[$groupId][$state];
        }
        $this->_groupUsers[$groupId] = array(
            Model_UserGroup::STATE_INVITATE => array(),
            Model_UserGroup::STATE_SUBMIT => array(),
            Model_UserGroup::STATE_REFUSE => array(),
            Model_UserGroup::STATE_DELETE => array(),
        );
        list($_,$tableName) = $this->getTableName($groupId);
        $sql = 'SELECT user_id,state,create_time FROM ' .$tableName;
        $sql.= ' WHERE group_id = :group_id ';
        $sql.= ' ORDER BY create_time';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(":group_id",$groupId,$this->_data_types['group_id']);
        $stmt->execute();
        $createTimes = array();
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            if($state == Model_UserGroup::STATE_CANCEL) {
                continue;
            }
            $createTimes[(int)$row['user_id']] = array(
                'state' => (int)$row['state'],
                'create_time' => (int)$row['create_time'],
            );
        }
        $ids = array_keys($createTimes);
        $users = $this->_storage->User->primary($ids);
        $tokens = $this->_storage->UserToken->getTokens($ids);
        foreach($tokens as $id => $token){
            $user = $users[$id];
            $thisState = $createTimes[$id]['state'];
            $user['id'] = $token;
            $user['create_time'] = $createTimes[$id]['create_time'];
            $this->_groupUsers[$groupId][$thisState][] = $user;
        }
        return $this->_groupUsers[$groupId][$state];
    }
    
    /**
     * 指定グループのメンバー情報を取得する。
     * 時間が指定された場合、更新があるユーザのみ取得。
     * @param int $groupId
     * @param int $time
     * @return type
     * @throws \InvalidArgumentException
     */
    public function getList($groupId,$state=Model_UserGroup::STATE_SUBMIT,$time=0)
    {
        $this->checkInt($groupId);
        if(!is_int($time)||$time<0) {
            throw new \InvalidArgumentException();
        }
        if(!is_int($state)||!in_array($state,array_keys(Model_UserGroup::$_stateList))) {
            throw new \InvalidArgumentException();
        }
        $members = $this->getAll($groupId,$state);
        $ret = array();
        foreach($members as $id => $member) {
            if((int)$member['update_time'] > $time) {
                unset($member['push_id']);
                unset($member['update_time']);
                $ret[$id] = $member;
            }
        }
        return array(count($members),$ret);
    }
    
    /**
     * グループ参加済みユーザの一覧
     * @param type $groupId
     * @param type $time
     */
    public function getMemberList($groupId,$time=0)
    {
        return $this->getList($groupId,Model_UserGroup::STATE_SUBMIT,$time);
    }
    
    /**
     * グループ招待中ユーザの一覧
     * @param type $groupId
     * @param type $time
     */
    public function getInvitationList($groupId,$time=0)
    {
        return $this->getList($groupId,Model_UserGroup::STATE_INVITATE,$time);
    }
    
    /**
     * グループ参加済みユーザのIDトークンを取得する。
     * ユーザが既に退出している場合は空配列が帰る。
     * @param type $userId
     * @param type $groupId
     * @return type
     */
    public function getTokens($userId,$groupId)
    {
        $this->checkInt($userId,$groupId);
        if(!$this->checkGroupAtUserId($groupId,$userId)) {
            return array();
        }
        $members = $this->getAll($groupId);
        $tokens = array();
        foreach($members as $member) {
            $tokens[] = $member['id'];
        }
        return $tokens;
    }
    
    /**
     * 既にグループに入っているかチェック
     * @param int $groupId
     * @param int $userId
     * @param bool $memberOnly
     * @return int
     * @throws \InvalidArgumentException
     */
    public function checkGroupAtUserId($groupId,$userId,$memberOnly=true)
    {
        $this->checkInt($groupId,$userId);
        $ret = $this->checkGroupAtUserIds($groupId, array($userId), $memberOnly);
        return isset($ret[$userId]) ? $ret[$userId] : null;
    }
    
    /**
     * 既にグループに入っているかチェック
     * @param int $groupId
     * @param array $userIds
     * @return type
     * @throws \InvalidArgumentException
     */
    public function checkGroupAtUserIds($groupId,$userIds,$memberOnly=true)
    {
        if(!$groupId||!is_int($groupId)) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($groupId);
        $ids = array();
        $ret = array();
        foreach($userIds as $id) {
            if(isset($this->_checkGroupUserIds[$id])) {
                $ret[$id] = $this->_checkGroupUserIds[$id];
            } else {
                $ids[] = $id;
            }   
        }
        if(!$ids) {
            return $ret;
        }
        
        $sql = 'SELECT user_id,state FROM ' . $tableName . ' WHERE user_id IN ('.implode(',',$ids).') AND group_id = :group_id';
        if($memberOnly) {
            $sql .= ' AND state = ' . Model_UserGroup::STATE_SUBMIT;
        }
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':group_id',$groupId,$this->_data_types['group_id']);
        $stmt->execute();
        while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $userId = (int)$row['user_id'];
            $ret[$userId] = (int)$row['state'];
        }
        return $ret;
    }
    
    
}

