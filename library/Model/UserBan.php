<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserBan extends Model
{
    /**
     * 有効
     */
    const AVAILABLE_TRUE = 1;
    /**
     * 無効
     */
    const AVAILABLE_FALSE = 0;
    
    protected $_table_name = 'user_ban';
    protected $_data_types = array(
        'id'            => \PDO::PARAM_INT,
        'available'     => \PDO::PARAM_INT,
        'reason'        => \PDO::PARAM_STR,
        'start_time'    => \PDO::PARAM_INT,
        'end_time'      => \PDO::PARAM_INT,
        'create_time'   => \PDO::PARAM_INT,
        'update_time'   => \PDO::PARAM_INT,
    );
    
    public function insert($keys,$params)
    {
        $values = array();
        $binds = array();
        foreach($params as $param) {
            $str = array();
            $count = count($keys);
            for($i=0;$i<$count;++$i) {
                $column = $keys[$i];
                $key = ':'.$column . $i;
                $str[] = $key;
                $binds[$key] = array($param[$column],$this->_data_types[$column]);
            }
            $values[] = "(" . implode(',',$str) . ")";
        }
        $sql = "INSERT INTO " . $this->_table_name . " (". implode(",",$keys) .") ";
        $sql.= " VALUES ".implode(",",$values);
        $sql.= ' ON DUPLICATE KEY UPDATE available=VALUES(available),reason=VALUES(reason),start_time=VALUES(start_time),end_time=VALUES(end_time),update_time=VALUES(update_time)';
        $stmt = $this->_con->prepare($sql);
        foreach($binds as $key => $bind) {
            $stmt->bindValue($key,$bind[0],$bind[1]);
        }
        return $stmt->execute();
    }
    
    /**
     * UserBanのレコードをUserBanHistoryにコピーして削除
     * @param type $ids
     * @return boolean
     */
    public function removes($ids)
    {
        if(!$ids){
            return true;
        }
        $this->_storage->UserBanHistory->copy($ids);
        //削除処理
        $this->deletePrimary($ids);
    }
}
