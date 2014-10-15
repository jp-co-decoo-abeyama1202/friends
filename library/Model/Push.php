<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_Push extends Model {
    const TYPE_REQUEST = 1;
    const TYPE_FRIEND = 10;
    const TYPE_MESSAGE = 20;
    const TYPE_GROUP_INVITE = 30;
    const TYPE_GROUP_MESSAGE = 40;
    const TYPE_TEST = 99;
    
    const RESULT_NONE = 0;
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
        'result_value' => \PDO::PARAM_STR,
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
    
    /**
     * PUSH送信 from -> to
     * @param int $fromId
     * @param int $toId
     * @param int $type
     * @param int $result
     */
    public function add($fromId,$toId,$type,$result,$resultValue="")
    {
        //DBに登録
        $values = array(
            'from_id' => $fromId,
            'to_id' => $toId,
            'type' => $type,
            'result' => $result,
            'result_value' => $resultValue,
            'create_time' => time()
        );
        return $this->insertOne($values);
    }
}

