<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_PushQueue extends Model {
    
    protected $_push;
    protected $_table_name = 'push_queue';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'from_id'  => \PDO::PARAM_INT,
        'to_id'  => \PDO::PARAM_INT,
        'message' => \PDO::PARAM_STR,
        'type' => \PDO::PARAM_INT,
        'create_time' => \PDO::PARAM_INT,
    );
    
    function __construct(Storage $storage) {
        parent::__construct($storage);
        $this->_push = new \library\Push($storage->getConfig()->getPushConfig());
    }
    
    public static $_message_list = array(
        Model_Push::TYPE_REQUEST => '%%NAME%%さんからフレンド申請が届きました',
        Model_Push::TYPE_FRIEND => '%%NAME%%さんとフレンドになりました',
        Model_Push::TYPE_MESSAGE => '%%NAME%%:%%MESSAGE%%',
        Model_Push::TYPE_GROUP_INVITE => '%%NAME%%さんからグループへの招待が届きました',
        Model_Push::TYPE_GROUP_MESSAGE => '',//グループ送信判別用
        Model_Push::TYPE_TEST => 'テストメッセージです',
    );
    /**
     *リクエスト通知
     * @var array
     */
    public static $_type_request = array(
        Model_Push::TYPE_REQUEST,Model_Push::TYPE_FRIEND,Model_Push::TYPE_GROUP_INVITE
    );
    /**
     * メッセージ通知
     * @var array
     */
    public static $_type_message = array(
        Model_Push::TYPE_MESSAGE
    );
    
    /**
     * PUSH送信キュー登録 from -> to
     * @param type $fromId
     * @param type $toId
     * @param type $type
     */
    public function add($fromId,$toId,$groupId,$type,$message="")
    {
        $this->checkInt($fromId);
        
        if(!in_array($type,array_keys(self::$_message_list))) {
            throw new InvalidArgumentException();
        }
        if($type === Model_Push::TYPE_GROUP_MESSAGE) {
            //グループへ送信
            //グループ取得
            $group = $this->_storage->Group->getGroupOrFail($toId);
            //グループメンバーか？
            if(!$this->_storage->Group->checkUser($toId,$fromId)) {
                throw new ErrorException();
            }
            $memberIds = $this->_storage->GroupUser->getMemberIds($toId);
            $users = $this->_storage->User->primary($memberIds);
            foreach($memberIds as $memberId) {
                if($fromId === $memberId) {
                    continue;
                }
                //1人1人追加していく
                $to = $users[$memberId];
                $option = $this->_storage->UserOption->primaryOne($memberId);
                $subOption = $this->_subOptionGet($type,$memberId,$toId);
                if($to['push_id'] && $this->_optionCheck(Model_Push::TYPE_MESSAGE,$option,$subOption)) {
                    $this->_add($fromId,$memberId,Model_Push::TYPE_MESSAGE,$message);
                }
            }
        } else {
            $users = $this->_storage->User->primary(array($fromId,$toId));
            $from = $users[$fromId];
            $to = $users[$toId];
            $option = $this->_storage->UserOption->primaryOne($toId);
            $subOption = $this->_subOptionGet($type,$fromId,$toId);
            
            if($to['push_id'] && $this->_optionCheck(Model_Push::TYPE_MESSAGE,$option,$subOption)) {
                $this->_add($fromId,$toId,$type,$message);
            }
        }
        return true;
    }
    
    /**
     * 個別PUSH設定を取得
     * @param int $type
     * @param int $userId
     * @param int $subId
     * @return array|null
     */
    protected function _subOptionGet($type,$userId,$subId)
    {
        if($type === Model_Push::TYPE_MESSAGE) {
            return $this->_storage->UserFriend->get($userId,$subId);
        }
        if($type === Model_Push::TYPE_GROUP_MESSAGE) {
            return $this->_storage->UserGroup->get($userId,$subId);
        }
        return null;
    }
    
    /**
     * オプション内容を確認し、PUSH通知を送るかを判別
     * @param int $type
     * @param array $option
     * @param array $subOption
     * @return boolean
     */
    protected function _optionCheck($type,$option,$subOption)
    {
        if(in_array($type,self::$_type_request)) {
            return $option['push_friend']==Model_UserOption::FLAG_ON ? true : false;
        } else if(in_array($type,self::$_type_message)) {
            $check1 = $option['push_chat']==Model_UserOption::FLAG_ON;
            $check2 = $subOption ? $subOption['push_chat']==Model_UserOption::FLAG_ON : true;
            return $check1 && $check2;
        }
        return false;
    }
    
    protected function _add($fromId,$toId,$type,$message)
    {
        $this->checkInt($fromId,$toId);
        if(!in_array($type,array_keys(self::$_message_list))) {
            throw new InvalidArgumentException();
        }
        //DBに登録
        $values = array(
            'from_id' => $fromId,
            'to_id' => $toId,
            'message' => $message,
            'type' => $type,
            'create_time' => time()
        );
        return $this->insertOne($values);
    }


    /**
     * Cronで使用。Queueに登録されたPUSHを実際に送信する
     */
    public function send()
    {
        if($this->_storage->PushQueueLock->check()) {
            return;
        }
        $this->_storage->PushQueueLock->on();
        $queues = $this->getAll();
        foreach($queues as $queue) {
            $failed = "";
            $id = (int)$queue['id'];
            $fromId = (int)$queue['from_id'];
            $toId = (int)$queue['to_id'];
            $type = (int)$queue['type'];
            
            $users = $this->_storage->User->primary(array($fromId,$toId));
            $from = $users[$fromId];
            $to = $users[$toId];
            
            //ユーザが存在しない or 削除済みなら送信しない
            if(!$from || !$to || $from['state'] == Model_User::STATE_INVALID || $to['state'] == Model_User::STATE_INVALID ) {
                $failed = $queue;
            }
            
            $device = $to['device'] == Model_User::DEVICE_IOS ? Push::TYPE_IOS : Push::TYPE_ANDROID;
            
            if(!$failed && $device && $to['push_id']) {
                //メッセージのカット処理
                $message = $queue['message'];
                
                if(\mb_strlen($message) > 50) {
                    $message = \mb_strcut($message,0,49) . "...";
                }
                
                //PUSH送信
                try {
                    $bool = $this->_push->send(
                        $device,
                        $to['push_id'],
                        $this->_push->message(str_replace('%%MESSAGE%%',$message,str_replace('%%NAME%%',$from['name'],self::$_message_list[$type])))
                        ->badge(3)
                        ->sound(null)
                    );
                } catch(Exception $e) {
                    $failed = $queue;
                    $failed['exception'] = $e->getMessage();
                }
            }
            $result = $failed ? Model_Push::RESULT_FAILED : Model_Push::RESULT_SUCCESS;
            $resultValue = $failed ? serialize($failed) : "";
            //結果テーブルに登録
            $this->_storage->Push->add($fromId,$toId,$type,$result,$resultValue);
            //レコード削除
            $this->deletePrimaryOne($id);
            sleep(1);
        }
        $this->_storage->PushQueueLock->off();
    }
}

