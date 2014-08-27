<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_Report extends Model {
    protected $_table_name = 'report';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'user_id' => \PDO::PARAM_STR,
        'reporter_id' => \PDO::PARAM_STR,
        'message' => \PDO::PARAM_STR,
        'create_time'  => \PDO::PARAM_INT,
        'login_time'  => \PDO::PARAM_INT,
    );
    
    public function search($values)
    {
        if(!$values) {
            return $this->getAll();
        }

        $binds = array();
        $where = array();
        
        //検索条件を纏める
        
        //user_id 違反者ID
        if(isset($values['user_id']) && $values['user_id']) {
            $id = $values['user_id'];
            if(!is_numeric($id)) {
                //tokenである可能性
                $user = $this->_storage->User->getDataFromToken($id);
                if(!$user) {
                    //存在しないユーザ　つまり　データは存在しない
                    return array();
                }
                $id = (int)$user['id'];
            }
            $binds[':user_id'] = $id;
            $where[] = 'user_id = :user_id';
        }
        //reporter_id 報告者ID
        if(isset($values['reporter_id']) && $values['reporter_id']) {
            $id = $values['reporter_id'];
            if(!is_numeric($id)) {
                //tokenである可能性
                $user = $this->_storage->User->getDataFromToken($id);
                if(!$user) {
                    //存在しないユーザ　つまり　データは存在しない
                    return array();
                }
                $id = (int)$user['id'];
            }
            $binds[':reporter_id'] = $id;
            $where[] = 'reporter_id = :reporter_id';
        }
        //start_time & end_time 報告時間
        //start_time
        if(isset($values['start_time']) && $values['start_time']) {
            $binds[':start_time'] = $values['start_time'];
            $where[] = 'create_time>=:start_time';
        }
        //end_time
        if(isset($values['end_time']) && $values['end_time']) {
            $binds[':end_time'] = $values['end_time'];
            $where[] = 'create_time<=:end_time';
        }
        //message 報告文（部分一致）
        if(isset($values['message']) && $values['message']) {
            $binds[':message'] = '%'.$values['message'].'%';
            $where[] = 'message LIKE :message';
        }
        if(!$where) {
            return $this->getAll();
        }
        
        $sql = 'SELECT * FROM ' . $this->_table_name . ' WHERE 1=1 AND ' . implode(' AND ',$where) . ' ORDER BY create_time DESC';
        $stmt = $this->_con->prepare($sql);
        foreach($binds as $key => $value) {
            $stmt->bindValue($key,$value);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

