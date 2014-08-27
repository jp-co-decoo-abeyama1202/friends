<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserBanHistory extends Model
{
    protected $_table_name = 'user_ban_history';
    protected $_data_types = array(
        'id'            => \PDO::PARAM_INT,
        'user_id'     => \PDO::PARAM_INT,
        'reason'        => \PDO::PARAM_STR,
        'start_time'    => \PDO::PARAM_INT,
        'end_time'      => \PDO::PARAM_INT,
        'create_time'   => \PDO::PARAM_INT,
    );
    
    public function getList($userId) 
    {
        $sql = 'SELECT * FROM ' . $this->_table_name . ' WHERE user_id = :user_id ORDER BY create_time DESC';
        $stmt = $this->_con->prepare($sql);
        $stmt->bindValue(':user_id',$userId,$this->_data_types['user_id']);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    /**
     * user_banからuser_ban_historyにコピー処理を行う
     * @param type $ids
     */
    public function copy($ids)
    {
        if(!$ids) {
            return true;
        }
        $sql = 'INSERT INTO user_ban_history (user_id,reason,start_time,end_time,create_time) ';
        $sql.= ' SELECT id,reason,start_time,end_time,? FROM user_ban WHERE id IN ('.implode(',',$ids).')';
        $stmt = $this->_con->prepare($sql);
        return $stmt->execute(array(time()));
    }
}
