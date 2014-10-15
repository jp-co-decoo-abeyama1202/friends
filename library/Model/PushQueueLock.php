<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_PushQueueLock extends Model {
    protected $_table_name = 'push_queue_lock';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'create_time' => \PDO::PARAM_INT,
    );
    
    public function on()
    {
        $values = array(
            'id' => 1,
            'create_time' => time()
        );
        return $this->insertOne($values);
    }
    
    public function off()
    {
        return $this->deletePrimaryOne(1);
    }
    
    public function check()
    {
        return $this->primaryOne(1) ? true : false;
    }
}

