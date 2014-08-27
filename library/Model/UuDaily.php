<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UuDaily extends Model {
    protected $_table_name = 'uu_daily';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'create_time' => \PDO::PARAM_INT,
    );
    
    function __construct(Storage $storage)
    {
        parent::__construct($storage);
        //日別なテーブルを作り上げる
        $tableName = 'uu_' . date('Ymd');
        $query = 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' LIKE uu_daily';
        $stmt = $this->_con->prepare($query);
        $stmt->execute();
        $this->_table_name = $tableName;
    }
    
    function add($id)
    {
        $sql = 'INSERT INTO ' . $this->_table_name . ' (id,create_time) VALUES (:id,:create_time)';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':id',$id,$this->_data_types['id']);
        $stmt->bindValue(':create_time',time(),$this->_data_types['create_time']);
        return (bool)$stmt->execute();
    }
}

