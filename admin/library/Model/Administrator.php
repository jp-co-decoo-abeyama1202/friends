<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library\admin;
class Model_Administrator extends \library\Model {
    protected $_table_name = 'user_memo';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'name' => \PDO::PARAM_STR,
        'password' =>  \PDO::PARAM_STR,
        'memo' => \PDO::PARAM_STR,
        'update_time'   => \PDO::PARAM_INT,
    );
    
}

