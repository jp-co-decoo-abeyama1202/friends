<?php
/**
 * 他ユーザのブロックの追加、削除を行う
 */
require_once('/home/homepage/html/public/friends/inc/define.php');
/*
if(IS_TEST) {
    $test_params = array(
        'user_id'   => 'oKLIfBrszDiZAxmEPmvrTm4jfCPz9c',
        'block_id' => 'rIzsn5DOZlVeoBrEXXNoPLLnlac8mJ',
        'mode' => 'add',
    );
    $_POST['params'] = json_encode($test_params);
}
*/
try {
    //json受け取り
    $json = isset($_POST['params']) ? $_POST['params'] : '';
    $params = json_decode($json,true);
    //存在チェック
    $token = isset($params['user_id']) ? $params['user_id'] : null;
    $blocktoken = isset($params['block_id']) ? $params['block_id'] : null;
    $mode = isset($params['mode']) ? $params['mode'] : null;
    if(is_null($token)||is_null($blocktoken)||is_null($mode)||!in_array($mode,\library\Model_UserBlock::$modeList)) {
        throw new InvalidArgumentException();
    }
    $user = $storage->User->getDataFromToken($token);
    $block = $storage->User->getDataFromToken($blocktoken);
    $id = (int)$user['id'];
    $blockId = (int)$block['id'];
    $check = $storage->UserBlock->check($id,$blockId);
    if($mode === \library\Model_UserBlock::MODE_ADD) {
        //追加
        if($check) {
            return \library\Response::json();
        }
        $storage->beginTransaction();
        //ブロック追加
        $storage->UserBlock->add($id,$blockId);
        //ブロッカー追加
        $storage->UserBlocker->add($blockId,$id);
        //フレンド状態か
        if($storage->UserFriend->checkFriend($id,$blockId)) {
            //フレンド情報削除
            $storage->UserFriend->delete($id,$blockId);
        }
        //拒否状態にする
        $requestF = $storage->UserRequestFrom->get($id,$blockId);
        $requestT = $storage->UserRequestFrom->get($blockId,$id);
        
        //相手から送られてきた申請を拒否した状態に
        if($requestT) {
            $messageId = (int)$requestT['message_id'];
            $storage->UserRequestMessage->update($messageId,$blockId,\library\Model_UserRequestMessage::CONDUCT_REFUSE_MESSAGE);
        } else {
            $messageId = $storage->UserRequestMessage->add($blockId,\library\Model_UserRequestMessage::CONDUCT_REFUSE_MESSAGE);
        }
        $storage->UserRequestFrom->add($blockId, $id, $messageId, \library\Model_UserRequestFrom::STATE_REFUSE, \library\Model_UserRequestFrom::DELETE_ON);
        $storage->UserRequestTo->add($id, $blockId, $messageId, \library\Model_UserRequestFrom::STATE_REFUSE, \library\Model_UserRequestFrom::DELETE_ON);
        
        //こちらから送った申請を取り消し状態に
        if($requestF) {
            $messageId = (int)$requestF['message_id'];
            if((int)$requestF['state'] === \library\Model_UserRequestFrom::STATE_REFUSE) {
                //こちらが申請を拒否していた場合はそちらを優先する
                $storage->UserRequestMessage->update($messageId,$id,\library\Model_UserRequestMessage::CONDUCT_REFUSE_MESSAGE);
                $storage->UserRequestFrom->add($id, $blockId, $messageId, \library\Model_UserRequestFrom::STATE_REFUSE, \library\Model_UserRequestFrom::DELETE_ON);
                $storage->UserRequestTo->add($blockId, $id, $messageId, \library\Model_UserRequestFrom::STATE_REFUSE, \library\Model_UserRequestFrom::DELETE_ON);
            } else {
                //こちらの申請は取り消し状態
                $storage->UserRequestMessage->update($messageId,$id,\library\Model_UserRequestMessage::CONDUCT_CANCELL_MESSAGE);
                $storage->UserRequestFrom->add($id, $blockId, $messageId, \library\Model_UserRequestFrom::STATE_CANCELL, \library\Model_UserRequestFrom::DELETE_ON);
                $storage->UserRequestTo->add($blockId, $id, $messageId, \library\Model_UserRequestFrom::STATE_CANCELL, \library\Model_UserRequestFrom::DELETE_ON);
            }
        } else {
            //未登録
            $messageId = $storage->UserRequestMessage->add($id,\library\Model_UserRequestMessage::CONDUCT_CANCELL_MESSAGE);
            //こちらからの申請は取り消し状態に。
            $storage->UserRequestFrom->add($id, $blockId, $messageId, \library\Model_UserRequestFrom::STATE_CANCELL, \library\Model_UserRequestFrom::DELETE_ON);
            $storage->UserRequestTo->add($blockId, $id, $messageId, \library\Model_UserRequestFrom::STATE_CANCELL, \library\Model_UserRequestFrom::DELETE_ON);
        }
        $storage->commit();
    } else if($mode === \library\Model_UserBlock::MODE_DEL) {
        //削除
        if(!$check) {
            //登録されていない
            throw new BadMethodCallException();
        }
        $storage->beginTransaction();
        $storage->UserBlock->delete($id,$blockId);
        $storage->UserBlocker->delete($blockId,$id);
        $storage->commit();
    }
    return \library\Response::success();
} catch(Exception $e) {
    if($storage->isTransaction()) {
        $storage->rollback();
    }
    return \library\Response::error($e);
}

