<?php

/**
 * グループごとの所属ユーザ
 *
 * @author Administrator
 */
namespace library\admin;
class Model_GroupUser extends \library\Model_GroupUser {
    /**
     * $groupIdのグループに入っているユーザ情報を全取得
     * @param int $groupId
     * @return array()
     * @throws \InvalidArgumentException
     */
    public function getAll($groupId,$state=\library\Model_UserGroup::STATE_SUBMIT)
    {
        $this->checkInt($groupId);
        if(!is_int($state)||!in_array($state,array_keys(\library\Model_UserGroup::$_stateList))) {
            throw new \InvalidArgumentException();
        }
        if(isset($this->_groupUsers[$groupId])) {
            return $this->_groupUsers[$groupId][$state];
        }
        $this->_groupUsers[$groupId] = array(
            \library\Model_UserGroup::STATE_INVITATE => array(),
            \library\Model_UserGroup::STATE_SUBMIT => array(),
            \library\Model_UserGroup::STATE_REFUSE => array(),
            \library\Model_UserGroup::STATE_DELETE => array(),
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
            if($state == \library\Model_UserGroup::STATE_CANCEL) {
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
            //$user['id'] = $token;
            $user['create_time'] = $createTimes[$id]['create_time'];
            $this->_groupUsers[$groupId][$thisState][] = $user;
        }
        return $this->_groupUsers[$groupId][$state];
    }
}

