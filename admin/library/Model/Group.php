<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library\admin;
class Model_Group extends \library\Model_Group 
{
    /**
     * 検索＆一覧の内容を取得する
     * @param array $searchValue
     * @param array
     */
    public function search($option)
    {
        $binds = array();
        $where = array();
        
        if(isset($option[':id'])) {
            $binds[':id'] = $option[':id'];
            if(is_numeric($option[':id'])) {
                $where[] = 'id=:id';
            }
        }
        if(isset($option[':name']) && $option[':name']) {
            $binds[':name'] = '%'.$option[':name'].'%';
            $where[] = 'name LIKE :name';
        }
        if(isset($option[':create_user_id']) && $option[':create_user_id']) {
            $binds[':create_user_id'] = $option[':create_user_id'];
            $where[] = 'create_user_id=:create_user_id';
        }
        if(isset($options[':delete_flag']) && (bool)$options[':delete_flag']) {
            $where[] = 'delete_flag='.\library\Model_Group::DELETE_ON;
        }
        if(isset($option[':min_create_time']) && $option[':min_create_time']) {
            $binds[':min_create_time'] = $option[':min_create_time'];
            $where[] = 'create_time>=:min_create_time';
        }
        if(isset($option[':max_create_time']) && $option[':max_create_time']) {
            $binds[':max_create_time'] = $option[':max_create_time'];
            $where[] = 'create_time<=:max_create_time';
        }
        
        $sql = 'SELECT * FROM group';
        $sql.= ' WHERE 1=1 ';
        if(!empty($where)) {
            $sql.= ' AND ' . implode(' AND ',$where);
        }
        $stmt = $this->_con->prepare($sql);
        foreach($binds as $key => $value) {
            $stmt->bindValue($key,$value);
        }
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}

