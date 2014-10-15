<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_Group extends Model {
    const DELETE_ON = 1;
    const DELETE_OFF = 0;
    const CREATE_LIMIT = 60;
    
    protected $_table_name = 'groups';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'create_user_id'  => \PDO::PARAM_INT,
        'name'  => \PDO::PARAM_STR,
        'image' => \PDO::PARAM_LOB,
        'delete_flag' => \PDO::PARAM_INT,
        'last_message_time' => \PDO::PARAM_INT,
        'last_message_id' => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    
    protected $_users = array();
    
    /**
     * グループを作成する
     * @param int $userId
     * @param string $name
     * @param array $memberIds
     * @return type
     * @throws \InvalidArgumentException
     */
    public function create($userId,$name,$image,$memberIds)
    {
        $this->checkInt($userId,$memberIds);
        $param = array(
            'create_user_id' => $userId,
            'name' => $name,
            'image' => $image,
            'delete_flag' => self::DELETE_OFF,
            'last_message_time' => 0,
            'last_message_id' => 0,
            'create_time' => time(),
            'update_time' => time(),
        );
        $this->insertOne($param);
        $groupId = (int)$this->lastInsertId();
        //メンバー登録
        $this->_storage->UserGroup->adds($memberIds,$groupId,$userId);
        $this->_storage->GroupUser->adds($groupId,$memberIds,$userId);
    }
    
    /**
     * グループを削除する
     * @param int $groupId
     * @return bool
     */
    public function delete($groupId)
    {
        $this->checkInt($groupId);
        $params = array(
            'delete_flag' => self::DELETE_ON,
            'update_time' => time(),
        );
        return $this->updatePrimaryOne($params, $groupId);
    }
    
    /**
     * グループを1件取得
     * @param int $groupId
     * @return array
     */
    public function getGroup($groupId)
    {
        $this->checkInt($groupId);
        $ret = $this->primaryOne($groupId);
        //メンバー取得
        if($ret) {
            $ret['member'] = $this->_storage->GroupUser->getAll($groupId);
        }
        return $ret;
    }
    
    /**
     * 指定したグループが存在しなければException
     * @param int $groupId
     * @return array
     * @throws OutOfBoundsException
     */
    public function getGroupOrFail($groupId)
    {
        $ret = $this->getGroup($groupId);
        if(!$ret) {
            throw new OutOfBoundsException();
        }
        return $ret;
    }
    
    /**
     * ユーザがそのグループに所属しているかチェック
     * @param type $groupId
     * @param type $userId
     */
    public function checkUser($groupId,$userId)
    {
        $this->checkInt($groupId,$userId);
        return $this->_storage->GroupUser->checkGroupAtUserId($groupId,$userId);
    }
    
    /**
     * グループ作成時間が経過しているかチェック
     * @param int $userId
     * @return bool
     */
    public function checkUserLastCreateTime($userId)
    {
        $this->checkInt($userId);
        $sql = "SELECT create_time FROM " . $this->_table_name . " WHERE create_user_id = :user_id ORDER BY create_time DESC LIMIT 1";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['create_user_id']);
        $stmt->execute();
        $lastCreateTime = (int)$stmt->fetchColumn();
        return time() > $lastCreateTime + self::CREATE_LIMIT ? true : false;
    }
    
    /**
     * グループへのユーザ追加処理
     * @param int $groupId
     * @param array $userIds
     * @param int $invitationId
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function addUsers($groupId,$userIds,$invitationId)
    {
        $this->checkInt($groupId,$invitationId,$userIds);
        $this->_storage->UserGroup->adds($userIds,$groupId,$invitationId);
        $this->_storage->GroupUser->adds($groupId,$userIds,$invitationId);
        return true;
    }
    
    /**
     * グループユーザの状態変更
     * @param int $groupId
     * @param int $userId
     * @param int $state
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function changeUserState($groupId,$userId,$state)
    {
        $this->checkInt($groupId,$userId,$state);
        if(!in_array($state,array(Model_UserGroup::STATE_SUBMIT,Model_UserGroup::STATE_REFUSE,Model_UserGroup::STATE_CANCEL))) {
            throw new \InvalidArgumentException();
        }
        $this->_storage->UserGroup->changeState($userId,$groupId,$state);
        $this->_storage->GroupUser->changeState($groupId,array($userId),$state);
        return true;
    }
    
    /**
     * ユーザの退出処理
     * @param int $groupId
     * @param int $userIds
     * @param int $deleterId
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function deleteUsers($groupId,$userIds,$deleterId)
    {
        $this->checkInt($groupId,$userIds,$deleterId);
        foreach($userIds as $userId) {
            $this->_storage->UserGroup->delete($userId,$groupId,$deleterId);
        }
        $this->_storage->GroupUser->deletes($groupId,$userIds,$deleterId);
        return true;
    }
    
    /**
     * メッセージを送信し、last_message_idを更新する
     * @param int $groupId
     * @param int $userId
     * @param int $message
     * @throws \InvalidArgumentException
     */
    public function addMessage($groupId,$userId,$message)
    {
        $this->checkInt($groupId,$userId);
        if(!$message) {
            throw new \InvalidArgumentException();
        }
        $messageId = (int)$this->_storage->GroupMessage->add($groupId,$userId,$message);
        if($messageId) {
            $this->updateLastMessage($groupId, $messageId);
        }
    }
    
    /**
     * last_message_idを更新する
     * @param type $groupId
     * @param type $messageId
     * @return type
     */
    public function updateLastMessage($groupId,$messageId)
    {
        $this->checkInt($groupId,$messageId);
        $params = array(
            'last_message_time' => time(),
            'last_message_id' => $messageId,
            'update_time' => time(),
        );
        return $this->updatePrimaryOne($params, $groupId);
    }
    
    /**
     * 指定ユーザの所属グループ情報を全て取得
     * @param type $userId
     * @return type
     */
    public function getGroupAll($userId)
    {
        $this->checkInt($userId);
        if(isset($this->_users[$userId])) {
            return $this->_users[$userId];
        }
        $userGroups = $this->_storage->UserGroup->getGroupAll($userId);
        $list = $this->primary(array_keys($userGroups));
        $ret = array();
        foreach($list as $row) {
            foreach($this->_data_types as $key => $param) {
                if($param === \PDO::PARAM_INT && isset($row[$key])) {
                    $row[$key] = (int)$row[$key];
                }
            }
            $ret[$row['id']] = $row;
        }
        $this->_users[$userId] = $ret;
        return $ret;
    }
    
    /**
     * 最終メッセージのIDを取得する
     * @param int $userId
     * @return array
     */
    public function getLastMessageIds($userId)
    {
        $groups = $this->getGroupAll($userId);
        $ret = array();
        foreach($groups as $groupId => $group) {
            if($group['last_message_id']) {
                $ret[$groupId] = $group['last_message_id'];
            }
        }
        return $ret;
    }
}

