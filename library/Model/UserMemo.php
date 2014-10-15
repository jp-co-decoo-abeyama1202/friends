<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library;
class Model_UserMemo extends Model
{
    protected $_table_name = 'user_memo';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'memo' => \PDO::PARAM_STR,
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
        $sql.= ' ON DUPLICATE KEY UPDATE memo=VALUES(memo),update_time=VALUES(update_time)';
        $stmt = $this->_con->prepare($sql);
        foreach($binds as $key => $bind) {
            $stmt->bindValue($key,$bind[0],$bind[1]);
        }
        return $stmt->execute();
    }
}
