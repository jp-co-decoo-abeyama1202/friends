<?php

/**
 * UserRequestFrom,UserRequestToの親クラス
 *
 * @author Administrato_idr
 */
namespace library;
class Model_UserRequest extends ShardingModel {
    
    /**
     * 申請中
     */
    const STATE_PENDING = 1;
    /**
     * 許可
     */
    const STATE_EXECUTE = 2;
    /**
     * 拒否
     */
    const STATE_REFUSE = 3;
    /**
     * 取り消し
     */
    const STATE_CANCELL = 4;
    /**
     * 削除OFF
     */
    const DELETE_OFF = 0;
    /**
     * 削除ON
     */
    const DELETE_ON = 1;
    const DEFAULT_COUNT = 30;
    
    const EXECUTE_MESSAGE = 'フレンズ申請を許可しました！';
    
    protected $_sharding = 50;
    protected $_sharding_key = 'user_id';
    public static $_statusList = array(
        self::STATE_PENDING => '申請中',
        self::STATE_EXECUTE => '許可',
        self::STATE_REFUSE => '拒否',
        self::STATE_CANCELL => 'キャンセル',
    );
    
    public static $_targetUserIdColumn = "";
    
    /**
     * 対象ユーザに対する申請情報を取得する
     * @param int $userId 
     * @param int $targetUserid
     * @return array
     * @throws \InvalidArgumentException
     */
    public function get($userId,$targetUserid)
    {
        if(!$userId||!is_int($userId)||!$targetUserid||!is_int($targetUserid)) {
            throw new \InvalidArgumentException();
        }
        $tColumn = static::$_targetUserIdColumn;
        $id = array(
            'user_id' => $userId,
            $tColumn => $targetUserid,
        );
        return $this->primaryOne($id);
    }
    
    /**
     * フレンドタブ用の申請情報一覧を取得する
     */
    public function getList($userId,$offset=0,$count=self::DEFAULT_COUNT)
    {
        throw new \BadMethodCallException();
    }
    
    /**
     * 対象ユーザの申請情報一覧を取得する
     * @param int $userId
     * @param int $state
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getRequests($userId,$state=null)
    {
        if(!$userId||!is_int($userId)) {
            throw new \InvalidArgumentException();
        }
        if($state && !in_array($state,array_keys(self::$_statusList))) {
            throw new \InvalidArgumentException();
        }
        list($i_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT * FROM ' . $tableName ;
        $sql.= ' WHERE '.$tableName.'.user_id = :user_id ';
        if($state) {
            $sql.= ' AND state = :state ';
        }
        $stmt = $this->_con->prepare($sql);
        error_log($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        if($state) {
            $stmt->bindValue(':state',$state,$this->_data_types['state']);
        }
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
    
    /**
     * 申請情報一覧からユーザID一覧を抜き出し戻す
     * @param int $userId
     * @param int $state
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getRequestUserIds($userId,$state=null)
    {
        if(!$userId||!is_int($userId)) {
            throw new \InvalidArgumentException();
        }
        if($state && !in_array($state,array_keys(self::$_statusList))) {
            throw new \InvalidArgumentException();
        }
        $requests = $this->getRequests($userId,$state);
        return $requests ? array_keys($requests) : array();
    }
    
    /**
     * 申請件数を取得する
     * @param int $userId
     * @param null|int $state
     * @return int
     */
    public function getRequestCounts($userId,$state=null)
    {
        if(!$userId||!is_int($userId)) {
            throw new \InvalidArgumentException();
        }
        list($i_,$tableName) = $this->getTableName($userId);
        $sql = 'SELECT count(*) FROM ' . $tableName ;
        $sql.= ' WHERE '.$tableName.'.user_id = :user_id ';
        if($state) {
            $sql.= ' AND state = :state';
        }
        $sql.= ' ORDER BY create_time DESC';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        if($state) {
            $stmt->bindValue(':state',$state,$this->_data_types['state']);
        }
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * 申請中の件数を返す
     * @param int $userId
     * @return int
     */
    public function getPendingCounts($userId)
    {
        return $this->getRequestCounts($userId, self::STATE_PENDING);
    }
    
    /**
     * 申請情報の登録を行う
     * @param type $userId
     * @param type $targetUserId
     * @param type $message
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function addRequest($userId,$targetUserId,$message)
    {
        if(!$userId||!is_int($userId)||!$targetUserId||!is_int($targetUserId)||!$message) {
            throw new \InvalidArgumentException();
        }
        //申請情報を取得し、メッセージの登録
        $request = $this->_storage->UserRequestFrom->get($userId,$targetUserId);
        if($request) {
            $nowState = (int)$request['state'];
            if(in_array($nowState,array(self::STATE_REFUSE,self::STATE_EXECUTE))) {
                //許可or拒否状態なのでフレンズ申請は出来ない
                throw new \ErrorException();
            }
            //message更新
            $messageId = (int)$request['message_id'];
            $this->_storage->UserRequestMessage->update($messageId,$userId,$message);
        } else {
            //message登録
            $messageId = (int)$this->_storage->UserRequestMessage->add($userId,$message);
        }
        //メッセージを登録したので申請情報を登録
        if(!$messageId) {
            //メッセージ登録失敗
            throw new \ErrorException();
        }
        
        //UserRequestFrom登録
        $resultF = $this->_storage->UserRequestFrom->add($userId,$targetUserId,$messageId,self::STATE_PENDING);
        //UserRequestTo登録
        $resultT = $this->_storage->UserRequestTo->add($targetUserId,$userId,$messageId,self::STATE_PENDING);
        if(!$resultF||!$resultT) {
            throw new \ErrorException();
        }
        return true;
    }
    
    /**
     * 申請情報の更新を行う
     * @param int $userId
     * @param int $targetUserId
     * @param int $state
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function updateRequest($userId,$targetUserId,$state)
    {
        if(!$userId||!is_int($userId)||!$targetUserId||!is_int($targetUserId)||!in_array($state,array_keys(self::$_statusList))) {
            throw new \InvalidArgumentException();
        }
        $storage = $this->_storage;
        //UserRequestFrom更新
        $resultF = $storage->UserRequestFrom->updateState($userId,$targetUserId,$state);
        //UserRequestTo更新
        $resultT = $storage->UserRequestTo->updateState($targetUserId,$userId,$state);
        if(!$resultF||!$resultT) {
            throw new ErrorException();
        }
        return true;
    }
    
    /**
     * フレンド成立処理
     * @param int $userId
     * @param int $targetUserId
     * @param int $messageId
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function executeRequest($userId,$targetUserId,$messageId)
    {
        if(!$userId||!is_int($userId)||!$targetUserId||!is_int($targetUserId)||!$messageId||!is_int($messageId)) {
            throw new \InvalidArgumentException();
        }
        
        $this->updateRequest($userId, $targetUserId, self::STATE_EXECUTE);
        
        //フレンドに登録
        $friendsId = $this->_storage->Friends->addFriend($userId,$targetUserId);
        
        //送信側が送ったメッセージを登録
        $message = $this->_storage->UserRequestMessage->getMessage($messageId,$userId);
        $this->_storage->Message->add($friendsId,$userId,$message['message']);
        //受信側のメッセージを登録
        $this->_storage->Message->add($friendsId,$targetUserId,self::EXECUTE_MESSAGE);
        return true;
    }
    
    /**
     * 申請情報テーブルへの登録を行う。
     * @param int $userId
     * @param int $targetUserId
     * @param int $messageId
     * @param int $state
     */
    public function add($userId,$targetUserId,$messageId,$state,$deleteFlag=self::DELETE_OFF)
    {
        if(!$userId||!is_int($userId)||!$targetUserId||!is_int($targetUserId)||!$messageId||!is_int($messageId)||!in_array($state,array_keys(self::$_statusList))||!in_array($deleteFlag,array(self::DELETE_OFF,self::DELETE_ON))) {
            throw new \InvalidArgumentException();
        }
        $tColumn = static::$_targetUserIdColumn;
        list($ids,$tableName) = $this->getTableName($userId);
        $sql = 'INSERT INTO ' . $tableName . " (user_id,$tColumn,message_id,state,create_time,update_time,delete_flag) VALUES (:user_id,:$tColumn,:message_id,:state,:create_time,:update_time,:delete_flag)";
        $sql.= ' ON DUPLICATE KEY UPDATE state=VALUES(state),delete_flag=VALUES(delete_flag),update_time=VALUES(update_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->bindValue(":$tColumn",$targetUserId,$this->_data_types[$tColumn]);
        $stmt->bindValue(':message_id',$messageId,$this->_data_types['message_id']);
        $stmt->bindValue(':state',$state,$this->_data_types['state']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        $stmt->bindValue(':update_time',time(),$this->_data_types['update_time']);
        $stmt->bindValue(':delete_flag',$deleteFlag,$this->_data_types['delete_flag']);
        return $stmt->execute();
    }
    
    /**
     * 申請テーブルのステータス更新を行う。
     * @param int $userId
     * @param int $targetUserId
     * @param int $state
     */
    public function updateState($userId,$targetUserId,$state)
    {
        if(!$userId||!is_int($userId)||!$targetUserId||!is_int($targetUserId)||!in_array($state,array_keys(self::$_statusList))) {
            throw new \InvalidArgumentException();
        }
        $tColumn = static::$_targetUserIdColumn;
        list($ids,$tableName) = $this->getTableName($userId);
        $values = array(
            'state' => $state,
            'update_time' => time(),
        );
        $id = array(
            'user_id' => $userId,
            $tColumn => $targetUserId,
        );
        return $this->updatePrimaryOne($values, $id, $userId);
    }
    
    /**
     * 申請テーブルの論理削除を行う。
     * @param int $userId
     * @param int $targetUserId
     */
    public function delete($userId,$targetUserId)
    {
        if(!$userId||!is_int($userId)||!$targetUserId||!is_int($targetUserId)) {
            throw new \InvalidArgumentException();
        }
        $tColumn = static::$_targetUserIdColumn;
        list($ids,$tableName) = $this->getTableName($userId);
        $values = array(
            'delete_flag' => self::DELETE_ON,
            'update_time' => time(),
        );
        $id = array(
            'user_id' => $userId,
            $tColumn => $targetUserId,
        );
        return $this->updatePrimaryOne($values, $id, $userId);
    }
}

