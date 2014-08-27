<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserComment extends Model {
    protected $_table_name = 'user_comment';
    protected $_primary_key = array(
        'user_id','comment_id'
    );
    protected $_data_types = array(
        'user_id' => \PDO::PARAM_INT,
        'comment_id' => \PDO::PARAM_INT,
        'title' => \PDO::PARAM_STR,
        'text' => \PDO::PARAM_STR,
        'create_time' => \PDO::PARAM_INT,
        'update_time' => \PDO::PARAM_INT,
    );
    
    /**
     * 1件insert
     * user_commentはON DUPLICATE KEY UPDATEを行う。
     * $paramの内容は以下の形を想定
     * array(
     *      $key1 => $value1,
     *      $key2 => $value2,
     * );
     * @param array $param
     * @return bool
     */
    public function insertOne($param)
    {
        $keys = array_keys($param);
        $values = array();
        $binds = array();
        foreach($param as $column => $value) {
            $key = ':'.$column;
            $binds[$key] = array($value,$this->_data_types[$column]);
        }
        $sql = "INSERT INTO " . $this->_table_name . " (". implode(",",$keys) .") ";
        $sql.= 'VALUES ('.implode(",",array_keys($binds)).')';
        $sql.= ' ON DUPLICATE KEY UPDATE title=VALUES(title),text=VALUES(text),update_time=VALUES(update_time)';
        $stmt = $this->_con->prepare($sql);
        foreach($binds as $key => $bind) {
            $stmt->bindValue($key,$bind[0],$bind[1]);
        }
        return $stmt->execute();
    }
    
    public function getCommentList($userId)
    {
        $userId = (int)$userId;
        $sql = 'SELECT comment_id,title,text FROM ' . $this->_table_name . ' WHERE user_id = :user_id ORDER BY comment_id ASC';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId);
        $stmt->execute();
        $list = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $ret = array();
        foreach($list as $values) {
            $ret['comment_'.$values['comment_id']] = array(
                'title' => $values['title'],
                'text'  => $values['text'],
            );
        }
        return $ret;
    }
}

