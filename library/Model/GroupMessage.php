<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_GroupMessage extends ShardingModel {
    const READ_ON = 1;
    const READ_OFF = 0;
    const READ_NONE = 2; //検索用　指定なし
    
    const DELETE_ON = 1;
    const DELETE_OFF = 0;
    
    const DEFAULT_COUNT = 30;
    
    const SENDER_MINE = 1;
    const SENDER_FRINDS = 2;
    
    const ADMIN_ID = 0;
    const ADMIN_INVITE = 1;
    const ADMIN_SUBMIT = 10;
    const ADMIN_DELETE_SELF = 20;
    const ADMIN_DELETE_OTHER = 21;
    const ADMIN_CHANGE_NAME = 30;
    const ADMIN_CHANGE_IMAGE = 31;
    
    public static $_searchMessageTypes = array(
        'match' => '完全一致',
        'l_match' => '前方一致',
        'r_match' => '後方一致',
        'p_match' => '部分一致',
    );
    
    public static $_searchReadFlag = array(
        self::READ_NONE => '指定なし',
        self::READ_OFF => '未読',
        self::READ_ON => '既読',
    );
    
    public static $_adminMessage = array(
        self::ADMIN_INVITE => '%%USER%%さんが%%TARGET%%さんを招待しました',
        self::ADMIN_SUBMIT => '%%USER%%さんが参加しました',
        self::ADMIN_DELETE_SELF => '%%USER%%さんが退出しました',
        self::ADMIN_DELETE_OTHER => '%%USER%%さんが%%TARGET%%さんを退出させました',
        self::ADMIN_CHANGE_NAME => '%%USER%%さんがグループタイトルを%%TARGET%%に変更しました',
        self::ADMIN_CHANGE_IMAGE => '%%USER%%さんがグループ画像を変更しました',
    );
    
    protected $_table_name = 'group_message';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'group_id'  => \PDO::PARAM_INT,
        'sender_id'  => \PDO::PARAM_INT,
        'message' => \PDO::PARAM_STR,
        'delete_flag' => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    protected $_sharding = 100;
    protected $_sharding_key = 'group_id';
    
    /**
     * メッセージを送信する
     * @param type $friendsId
     * @param type $senderId
     * @param type $message
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($groupId,$senderId,$message)
    {
        if(!$groupId||!is_int($groupId)||$senderId<0||!is_int($senderId)||!$message) {
            throw new \InvalidArgumentException();
        }
        $params = array(
            'group_id' => $groupId,
            'sender_id' => $senderId,
            'message' => $message,
            'create_time' => time(),
            'update_time' => time(),
        );
        $result = $this->insertOne($groupId,$params);
        
        $id = 0;
        if($result) {
            $id = $this->lastInsertId();
        }
        return $id;
    }
    
    public function addAdminMessage($groupId,$type,$name="",$target="")
    {
        $this->checkInt($groupId);
        if(!in_array($type,array_keys(self::$_adminMessage))) {
            throw new \InvalidArgumentException();
        }
        $message = str_replace('%%USER%%',$name,str_replace('%%TARGET%%',$target,self::$_adminMessage[$type]));
        return $this->add($groupId,self::ADMIN_ID,$message);
    }
    
    /**
     * $groupIdに該当するテーブルから、idが一致するレコードを引っ張る
     * @param int $groupId
     * @param array $ids
     * @return type
     * @throws \InvalidArgumentException
     */
    public function getMessageFromIds($groupId,$ids)
    {
        if(!$groupId||!is_int($groupId)) {
            throw new \InvalidArgumentException();
        }
        foreach($ids as $id) {
            if(!$id||!is_int($id)) {
                throw new \InvalidArgumentException();
            }
        }
        list($_,$tableName) = $this->getTableName($groupId);
        $sql = 'SELECT * FROM ' . $tableName . " WHERE id IN(".implode(',',$ids).")";
        $stmt = $this->_con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 既読にする
     * @param int $groupId
     * @param int $userId
     * @param array $ids
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function read($groupId,$userId,$ids)
    {
        return $this->_storage->MessageRead->read($groupId,$userId,$ids);
    }
    
    /**
     * 指定$groupIdのメッセージを全削除
     * @param int $groupId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function deleteAll($groupId)
    {
        if(!$groupId||!is_int($groupId)) {
            throw new \InvalidArgumentException();
        }
        $ids = array(
            $groupId => array(),
        );
        $values = array(
            'delete_flag' => self::DELETE_ON,
            'update_time' => time(),
        );
        return $this->updatePrimary($values, $ids);
    }
    
    /**
     * メッセージを取得する
     * @param int $groupId
     * @param int $offset
     * @param int $count
     * @return type
     * @throws \InvalidArgumentException
     */
    public function getMessage($userId,$groupId,$offset=0,$count=self::DEFAULT_COUNT)
    {
        $this->checkInt($userId,$groupId);
        if($offset<0||$count<0) {
            throw new \InvalidArgumentException();
        }
        //ユーザの所属期間を取得
        list($_m,$tableName) = $this->getTableName($groupId);
        list($_r,$readTableName) = $this->_storage->GroupMessageRead->getTableName($groupId);
        $sql = 'SELECT m.id,sender_id,message,COUNT(r.id) AS read_cnt,m.create_time,m.update_time FROM ' . $tableName . " as m ";
        $sql.= ' LEFT JOIN ' . $readTableName . ' as r ON m.group_id = r.group_id AND m.id = r.message_id';
        $sql.= ' WHERE m.group_id = :group_id AND m.delete_flag = :delete_flag ';
        $timeSql = array();
        $addGroupTimes = $this->_storage->UserGroupLog->getAddGroupTimes($userId,$groupId);
        foreach($addGroupTimes as $time) {
            if(!$time[0]) {
                continue;
            }
            if($time[1]) {
                $timeSql[] = 'm.create_time BETWEEN ' . $time[0] . ' AND ' . $time[1];
            } else {
                $timeSql[] = 'm.create_time >= ' . $time[0];
            }
        }
        if($timeSql) {
            $sql.= ' AND ( ' . implode(' OR ', $timeSql) . ' )';
        }
        $sql.= ' GROUP BY m.id ';
        $sql.= " ORDER BY m.create_time DESC,m.id DESC LIMIT " . $offset . "," . $count;
        //error_log($sql);
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':group_id',$groupId,$this->_data_types['group_id']);
        $stmt->bindValue(':delete_flag',self::DELETE_OFF,$this->_data_types['delete_flag']);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 全メッセージを取得する
     * @param int $friendsId
     * @return type
     * @throws \InvalidArgumentException
     */
    public function getAllMessage($groupId)
    {
        if(!$groupId||!is_int($groupId)) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($groupId);
        list($_r,$readTableName) = $this->_storage->GroupMessageRead->getTableName($groupId);
        $sql = 'SELECT *,COUNT(r.id) as read_cnt FROM ' . $tableName . " WHERE friends_id = :friends_id ";
        $sql = ' LEFT JOIN ' . $readTableName . ' as r ON as m.group_id = r.group_id AND m.id = r.message_id';
        $sql.= ' GROUP BY m.id ';
        $sql.= ' ORDER BY create_time DESC';
        error_log($sql);
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':friends_id',$friendsId,$this->_data_types['friends_id']);
        $stmt->execute();
        $ret = array();
        while($row = $stmt->fetch()) {
            $id = (int)$row['id'];
            $ret[$id] = $row;
        }
        return $ret;
    }
    
    /**
     * 指定されたグループの最終メッセージIDを取得する
     * @param int $groupId
     * @return int
     */
    public function getLastMessageId($groupId)
    {
        list($Ids,$tableName) = $this->getTableName($groupId);
        $sql = 'SELECT id FROM ' . $tableName . ' WHERE id IN ( SELECT MAX(id) FROM '.$tableName.' as m WHERE group_id = :group_id AND delete_flag = :delete_flag GROUP BY group_id)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':group_id',$groupId,$this->_data_types['group_id']);
        $stmt->bindValue(':delete_flag',self::DELETE_OFF,$this->_data_types['delete_flag']);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * 指定ユーザが指定グループで最後に取得したメッセージのIDを返す
     * @param int $userId
     * @param int $groupId
     * @return int
     */
    public function getLastMessageIdFromUserId($userId,$groupId)
    {
        $this->checkInt($userId,$groupId);
        list($Ids,$tableName) = $this->getTableName($groupId);
        $sql = 'SELECT id FROM ' . $tableName . ' WHERE id IN ';
        $sql.= '( SELECT MAX(id) FROM '.$tableName.' as m WHERE group_id = :group_id AND delete_flag = :delete_flag ';
        $addGroupTimes = $this->_storage->UserGroupLog->getAddGroupTimes($userId,$groupId);
        $timeSql = array();
        foreach($addGroupTimes as $time) {
            if(!$time[0]) {
                continue;
            }
            if($time[1]) {
                $timeSql[] = 'm.create_time BETWEEN ' . $time[0] . ' AND ' . $time[1];
            } else {
                $timeSql[] = 'm.create_time >= ' . $time[0];
            }
        }
        if($timeSql) {
            $sql.= ' AND ( ' . implode(' OR ', $timeSql) . ' )';
        }
        $sql.= ' GROUP BY group_id)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':group_id',$groupId,$this->_data_types['group_id']);
        $stmt->bindValue(':delete_flag',self::DELETE_OFF,$this->_data_types['delete_flag']);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * primaryした内容のうち、論理削除されていないものだけ抽出
     * @param array $ids
     * @return type
     */
    public function primaryNotDelete($ids)
    {
        $list = $this->primary($ids);
        $ret = array();
        foreach($list as $row) {
            if($row['delete_flag'] != self::DELETE_OFF) {
               continue;
            }
            $id = (int)$row['id'];
            $ret[] = $row;
        }
        return $ret;
    }
    
    public function getNewMessage($userId)
    {
        $this->checkInt($userId);
        //グループ情報取得
        $groups = $this->_storage->Group->getGroupAll($userId);
        $lastMessageIds = $this->_storage->Group->getLastMessageIds($userId);
        $userGroups = $this->_storage->UserGroup->getGroupAll($userId);
        $primaryKeys = array();
        foreach($lastMessageIds as $groupId => $messageId) {
            $messageId = (int)$userGroups[$groupId]['last_message_id'] ? $userGroups[$groupId]['last_message_id'] : $messageId;
            $primaryKeys[] = array(
                'id' => $messageId,
                'group_id' => $groupId,
            );
        }
        $messages = $this->primaryNotDelete($primaryKeys);
        $ret = array();
        $notRead = 0;
        foreach($messages as $row) {
            $groupId = (int)$row['group_id'];
            $senderId = (int)$row['sender_id'];
            $row['sender'] = $userId === $senderId ? self::SENDER_MINE : self::SENDER_FRINDS;
            $row['group'] = $groups[$groupId];
            //不要な内容を削除
            unset($row['sender_id']);
            unset($row['delete_flag']);
            $ret[] = $row;
        }
        return array($ret,$notRead);
    }
    
    /**
     * メッセージの検索を行う
     * @param type $values
     */
    public function search($values)
    {
        if(!$values) {
            //全部出力とかはしない
            return array();
        }
        $binds = array();
        $where = array();
        
        //まず検索条件を纏める
        
        //search_id
        if(isset($values['search_id']) && $values['search_id']) {
            //すごくありがたい
            $id = $values['search_id'];
            if(!is_int($id)) {
                //tokenである可能性
                $user = $this->_storage->User->getDataFromToken($id,false);
                if(!$user) {
                    //存在しないユーザ　つまり　データは存在しない
                    return array(array(),array());
                }
                $id = (int)$user['id'];
            }
            $binds[':sender_id'] = $id;
            $where[] = 'sender_id = :sender_id';
        }
        
        //search_text & search_type
        if(isset($values['search_text']) && $values['search_text']) {
            //恐怖のテキスト検索
            $text = $values['search_text'];
            $type = isset($values['search_type']) ? $values['search_type'] : 'match';
            switch($type) {
                case 'l_match':
                    //前方一致
                    $binds[':message'] = $text.'%';
                    $where[] = 'message LIKE :message';
                    break;
                case 'r_match':
                    //後方一致
                    $binds[':message'] = '%'.$text;
                    $where[] = 'message LIKE :message';
                    break;
                case 'p_match':
                    //部分一致
                    $binds[':message'] = '%'.$text.'%';
                    $where[] = 'message LIKE :message';
                    break;
                default:
                    $binds[':message'] = $text;
                    $where[] = 'message = :message';
            }
        }
        //search_start
        if(isset($values['search_start']) && $values['search_start']) {
            $binds[':search_start'] = $values['search_start'];
            $where[] = 'create_time>=:search_start';
        }
        //search_end
        if(isset($values['search_end']) && $values['search_end']) {
            $binds[':search_end'] = $values['search_end'];
            $where[] = 'create_time<=:search_end';
        }
        //search_read
        if(isset($values['search_read']) && $values['search_read'] != self::READ_NONE) {
            $binds[':search_read'] = $values['search_read'];
            $where[] = 'read_flag=:search_read';
        }
        
        if(!$where) {
            return array();
        }
        
        $ret = array();
        $sharding = $this->getSharding();
        for($i=0;$i<$sharding;++$i) {
            $tableName = $this->_table_name . '_' . $i;
            $sql = 'SELECT * FROM ' . $tableName . ' WHERE 1=1 AND ' . implode(' AND ',$where) . ' ORDER BY create_time DESC';
            $stmt = $this->_con->prepare($sql);
            foreach($binds as $key => $value) {
                $stmt->bindValue($key,$value);
            }
            $stmt->execute();
            while($row = $stmt->fetch()) {
                //一意なIDを作ろう
                //sharding 0埋め3桁 . id 0埋め10桁
                $pid = sprintf('%03d',$i) . sprintf('%010d',$row['id']);
                $ret[$pid] = $row;
                $sort[$pid] = (int)$row['create_time'];
            }
        }
        return $ret;
    }
}

