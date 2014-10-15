<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_Question extends Model {
    protected $_table_name = 'friends';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'title'  => \PDO::PARAM_INT,
    );
}
