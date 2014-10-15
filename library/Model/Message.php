<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_Message extends ShardingModel {
    const READ_ON = 1;
    const READ_OFF = 0;
    const READ_NONE = 2; //検索用　指定なし
    
    const DELETE_ON = 1;
    const DELETE_OFF = 0;
    
    const DEFAULT_COUNT = 30;
    
    const SENDER_MINE = 1;
    const SENDER_FRINDS = 2;
    
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
    
    protected $_table_name = 'message';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'friends_id'  => \PDO::PARAM_INT,
        'sender_id'  => \PDO::PARAM_INT,
        'message' => \PDO::PARAM_STR,
        'read_flag' => \PDO::PARAM_INT,
        'delete_flag' => \PDO::PARAM_INT,
        'create_time'  => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    protected $_sharding = 100;
    protected $_sharding_key = 'friends_id';
    
    /**
     * メッセージを送信する
     * @param type $friendsId
     * @param type $senderId
     * @param type $message
     * @return type
     * @throws \InvalidArgumentException
     */
    public function add($friendsId,$senderId,$message)
    {
        if(!$friendsId||!$senderId||!$message) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($friendsId);
        $sql = 'INSERT INTO ' . $tableName . ' (friends_id,sender_id,message,create_time,update_time) VALUES (:friends_id,:sender_id,:message,:create_time,:update_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':friends_id',$friendsId,$this->_data_types['friends_id']);
        $stmt->bindValue(':sender_id',$senderId,$this->_data_types['sender_id']);
        $stmt->bindValue(':message',$message,$this->_data_types['message']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        $stmt->bindValue(':update_time',time(),$this->_data_types['update_time']);
        $result = $stmt->execute();
        
        $id = 0;
        if($result) {
            $id = (int)$this->lastInsertId();
            $this->_storage->Friends->addMessage($friendsId,$id);
        }
        return $id;
    }
    
    /**
     * friendsIdに該当するテーブルから、idが一致するレコードを引っ張る
     * @param type $friendsId
     * @param type $ids
     * @return type
     * @throws \InvalidArgumentException
     */
    public function getMessageFromIds($friendsId,$ids)
    {
        if(!$friendsId||!$ids) {
            throw new \InvalidArgumentException();
        }
        list($_,$tableName) = $this->getTableName($friendsId);
        $sql = 'SELECT * FROM ' . $tableName . " WHERE id IN(".implode(',',$ids).")";
        $stmt = $this->_con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 既読にする
     * @param int $friendsId
     * @param array $ids
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function read($friendsId,$ids)
    {
        if(!$friendsId||!$ids) {
            throw new \InvalidArgumentException();
        }
        list($_,$tableName) = $this->getTableName($friendsId);
        $sql = 'UPDATE ' . $tableName . " SET read_flag = :read_flag,update_time = :update_time WHERE id IN(".implode(',',$ids).")";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':read_flag',self::READ_ON,$this->_data_types['read_flag']);
        $stmt->bindValue(':update_time',time(),$this->_data_types['update_time']);
        return (bool)$stmt->execute();
    }
    
    /**
     * 削除にする
     * @param int $friendsId
     * @param array $ids
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function delete($friendsId,$ids)
    {
        $this->checkInt($friendsId,$ids);
        list($_,$tableName) = $this->getTableName($friendsId);
        $sql = 'UPDATE ' . $tableName . " SET delete_flag = :delete_flag,update_time = :update_time WHERE id IN(".implode(',',$ids).")";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':delete_flag',self::DELETE_ON,$this->_data_types['delete_flag']);
        $stmt->bindValue(':update_time',time(),$this->_data_types['update_time']);
        if((bool)$stmt->execute()) {
            $id = $this->getLastMessageId($friendsId);
            $this->_storage->Friends->addMessage($friendsId,$id);
        } else {
            return false;
        }
        return true;
    }
    
    /**
     * 指定friendsIdのメッセージを全削除
     * @param int $friendsId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function deleteAll($friendsId)
    {
        if(!$friendsId) {
            throw new \InvalidArgumentException();
        }
        list($_,$tableName) = $this->getTableName($friendsId);
        $sql = 'UPDATE ' . $tableName . " SET delete_flag = :delete_flag,update_time = :update_time WHERE friends_id = :friends_id";
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':delete_flag',self::DELETE_ON,$this->_data_types['delete_flag']);
        $stmt->bindValue(':update_time',time(),$this->_data_types['update_time']);
        $stmt->bindValue(':friends_id',$friendsId,$this->_data_types['friends_id']);
        return (bool)$stmt->execute();
    }
    
    /**
     * メッセージを取得する
     * @param int $friendsId
     * @param int $offset
     * @param int $count
     * @return type
     * @throws \InvalidArgumentException
     */
    public function getMessage($friendsId,$offset=0,$count=self::DEFAULT_COUNT)
    {
        if(!$friendsId||$offset<0||$count<0) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($friendsId);
        $sql = 'SELECT id,sender_id,message,read_flag,create_time,update_time FROM ' . $tableName . " WHERE friends_id = :friends_id AND delete_flag = :delete_flag ORDER BY create_time DESC,id DESC LIMIT " . $offset . "," . $count;
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':friends_id',$friendsId,$this->_data_types['friends_id']);
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
    public function getAllMessage($friendsId)
    {
        if(!$friendsId) {
            throw new \InvalidArgumentException();
        }
        list($ids,$tableName) = $this->getTableName($friendsId);
        $sql = 'SELECT * FROM ' . $tableName . " WHERE friends_id = :friends_id ORDER BY create_time DESC ";
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
     * 指定されたフレンズ間の最終メッセージIDを取得する
     * @param int $friendsId
     * @return int
     */
    public function getLastMessageId($friendsId)
    {
        list($Ids,$tableName) = $this->getTableName($friendsId);
        $sql = 'SELECT id FROM ' . $tableName . ' WHERE id IN ( SELECT MAX(id) FROM '.$tableName.' as m WHERE friends_id = :friends_id AND delete_flag = :delete_flag GROUP BY friends_id)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':friends_id',$friendsId,$this->_data_types['friends_id']);
        $stmt->bindValue(':delete_flag',self::DELETE_OFF,$this->_data_types['delete_flag']);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
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
    
    /**
     * トークタブに表示する内容を取得
     * @param int $userId
     * @return type
     */
    public function getNewMessages($userId)
    {
        $this->checkInt($userId);
        //フレンド情報取得
        $friends = $this->_storage->Friends->getFriendsUserAll($userId);
        $lastMessageIds = $this->_storage->Friends->getLastMessageIds($userId);
        $primaryKeys = array();
        foreach($lastMessageIds as $friendsId => $messageId) {
            $primaryKeys[] = array(
                'id' => $messageId,
                'friends_id' => $friendsId,
            );
        }
        $messages = $this->primaryNotDelete($primaryKeys);
        $ret = array();
        $notRead = 0;
        foreach($messages as $row) {
            $friendsId = (int)$row['friends_id'];
            $senderId = (int)$row['sender_id'];
            $row['sender'] = $userId === $senderId ? self::SENDER_MINE : self::SENDER_FRINDS;
            $row['friend'] = $friends[$friendsId];
            //不要な内容を削除
            unset($row['friends_id']);
            unset($row['sender_id']);
            unset($row['delete_flag']);
            $ret[] = $row;
            if((int)$row['read_flag'] === self::READ_OFF && $senderId !== $userId) {
                ++$notRead;
            }
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
            if(!is_numeric($id)) {
                //tokenである可能性
                $user = $this->_storage->User->getDataFromToken($id);
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

