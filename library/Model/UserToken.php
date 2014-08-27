<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserToken extends Model {
    protected $_table_name = 'user_token';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'token' => \PDO::PARAM_STR,
    );
    
    /**
     * tokenからユーザIDを取得する
     * @param string $token
     * @return Array|null
     */
    public function getIdFromToken($token)
    {
        $query = 'SELECT id FROM ' . $this->_table_name .' WHERE token = :token';
        $stmt = $this->_con->prepare($query);
        $stmt->bindValue(':token',$token,$this->_data_types['token']);
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    public function getToken($userId)
    {
        $data = $this->primaryOne($userId);
        if($data) {
            return $data['token'];
        } else {
            return '';
        }
    }
    
    public function getTokens($ids)
    {
        $list = $this->primary($ids);
        $ret = array();
        foreach($list as $data) {
            $id = (int)$data['id'];
            $ret[$id] = $data['token'];
        }
        return $ret;
    }
    
    /**
     * ランダムな文字列を生成する
     * @param type $length
     * @return string
     */
    function getRandomString($length = 30)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $max = \strlen($chars) - 1;
        $ret = '';
        for ($i = 0; $i < $length; ++$i) {
            $ret .= $chars[\mt_rand(0, $max)];
        }
        return $ret;
    }
    
    /**
     * tokenを登録し、戻す
     * @param int $userId 
     */
    public function create($userId)
    {
        if(!$userId) {
            throw new \InvalidArgumentException();
        }
        do {
            $token = $this->getRandomString();
        }while(($this->checkTokenDuplicated($userId,$token)));
        
        $values = array(
            'id' => $userId,
            'token' => $token,
        );
        $this->insertOne($values);
        return $token;
    }
    
    /**
     * tokenが登録済みかチェック
     * @param type $userId
     * @param type $token
     */
    public function checkTokenDuplicated($userId,$token)
    {
        if(!$userId||!$token) {
            throw new \InvalidArgumentException();
        }
        $tokenId = $this->getIdFromToken($token);
        if($userId !== $tokenId && $tokenId !== 0) {
            //他のユーザが使用中のtoken
            return true;
        } else {
            return false;
        }
    }
}

