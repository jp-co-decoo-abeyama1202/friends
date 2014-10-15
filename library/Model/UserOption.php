<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserOption extends Model {
    const FLAG_ON = 1;
    const FLAG_OFF = 0;
    
    protected $_table_name = 'user_option';
    protected $_primary_key = 'user_id';
    protected $_data_types = array(
        'user_id'   => \PDO::PARAM_INT,
        'sex'  => \PDO::PARAM_INT,
        'min_age'  => \PDO::PARAM_INT,
        'max_age'  => \PDO::PARAM_INT,
        'country'  => \PDO::PARAM_INT,
        'area'  => \PDO::PARAM_INT,
        'view_friend' => \PDO::PARAM_INT,
        'view_refuse' => \PDO::PARAM_INT,
        'push_friend' => \PDO::PARAM_INT,
        'push_chat' => \PDO::PARAM_INT,
        'update_time'  => \PDO::PARAM_INT,
    );
    /**
     * 不要なものを削除したオプション情報を返す
     * @param int $userId
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getOption($userId)
    {
        if(!is_int($userId)||!$userId) {
            throw new \InvalidArgumentException();
        }
        $option = $this->primaryOne($userId);
        unset($option['user_id']);
        unset($option['update_time']);
        return $option;
    }
}

