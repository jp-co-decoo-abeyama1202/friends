<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_Push extends Model {
    const TYPE_REQUEST = 1;
    const TYPE_FRIEND = 2;
    const TYPE_MESSAGE = 3;
    const TYPE_TEST = 9;
    const RESULT_SUCCESS = 1;
    const RESULT_FAILED = 2;
    
    protected $_table_name = 'push';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'from_id'  => \PDO::PARAM_INT,
        'to_id'  => \PDO::PARAM_INT,
        'type' => \PDO::PARAM_INT,
        'result' => \PDO::PARAM_INT,
        'create_time' => \PDO::PARAM_INT,
    );
    
    function __construct(Storage $storage)
    {
        parent::__construct($storage);
        //日別なテーブルを作り上げる
        $tableName = 'push_' . date('Ymd');
        $query = 'CREATE TABLE IF NOT EXISTS ' . $tableName . ' LIKE push';
        $stmt = $this->_con->prepare($query);
        $stmt->execute();
        $this->_table_name = $tableName;
    }
}

