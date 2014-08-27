<?php

/**
 * Description of Test
 *
 * @author Administrator
 */
namespace library\admin;
class Model_Uu extends \library\Model {
    protected $_table_name = 'uu_daily';
    protected $_primary_key = 'id';
    protected $_data_types = array(
        'id'   => \PDO::PARAM_INT,
        'create_time' => \PDO::PARAM_INT,
    );
    
    /**
     * Uuを取得する
     * @param type $time
     * @return int
     */
    public function getUuCount($time = null)
    {
        $now = time();
        if(!$time) {
            $time = $now;
        }
        if(!is_numeric($time)) {
            //数字じゃないのでstrtotime
            $time = strtotime($time);
        }
        //指定時間の0時～23:59:59までに登録した人を集計
        $start = strtotime(date('Y/m/d 00:00:00',$time));
        $end = strtotime(date('Y/m/d 23:59:59',$time));
        $ret = array(
            'total' => 0,
            'device_' . \library\Model_User::DEVICE_IOS => 0,
            'device_' . \library\Model_User::DEVICE_ANDROID => 0,
            'sex_' . \library\Model_User::SEX_ALL => 0,
            'sex_' . \library\Model_User::SEX_MAN => 0,
            'sex_' .\library\Model_User::SEX_WOMAN => 0,
        );
        if($now >= $start && $now <= $end) {
            //今日の分
            $uus = $this->_storage->UuDaily->getAll();
            $ids = array();
            foreach($uus as $row) {
                $ids[] = (int)$row['id'];
            }
            if(!$ids) {
                return $ret;
            }
            $con = $this->_storage->getConnection('default');
            $sql = 'SELECT device,sex FROM user WHERE id IN ('.implode(',',$ids).')';
            $stmt = $con->prepare($sql);
            $stmt->execute();
            while($row = $stmt->fetch()) {
                $ret['total']++;
                $ret['device_'.$row['device']]++;
                $ret['sex_'.$row['sex']]++;
            }
        } else if($start >= $now){
            //未来の分
            //ない
        } else {
            //過去の分
            //集計済みデータを引っ張る
            $con = $this->_storage->getConnection("statistics");
            $stmt = $con->prepare('SELECT count FROM daily_uu WHERE regist_date = ?');
            $stmt->execute(array($start));
            $ret['total'] = (int)$stmt->fetchColumn();
            //device
            $stmt = $con->prepare('SELECT type,count FROM daily_uu_device WHERE regist_date = ?');
            $stmt->execute(array($start));
            while($row = $stmt->fetch()) {
                $ret['device_'.$row['type']] = (int)$row['count'];
            }
            //sex
            $stmt = $con->prepare('SELECT type,count FROM daily_uu_sex WHERE regist_date = ?');
            $stmt->execute(array($start));
            while($row = $stmt->fetch()) {
                $ret['sex_'.$row['type']] = (int)$row['count'];
            }
        }
        return $ret;
    }
    
    public function getUuCountMonthly($year,$month)
    {
        $now = strtotime(date('Y/m/d 00:00:00',time()));//当日の00:00:00を取得
        $year = (int)$year;
        $month = (int)$month;
        $date = $year."-".$month;
        //指定年月の1日と末日を取得
        $first = strtotime('first day of',$date);
        $last = strtotime('last day of',$date);
        
        $ret = array(
            'total' => 0,
            'device_' . \library\Model_User::DEVICE_IOS => 0,
            'device_' . \library\Model_User::DEVICE_ANDROID => 0,
            'sex_' . \library\Model_User::SEX_ALL => 0,
            'sex_' . \library\Model_User::SEX_MAN => 0,
            'sex_' .\library\Model_User::SEX_WOMAN => 0,
        );
        if($first > $now) {
            //未来のデータなどない
            return $ret;
        }
        
        //まず過去分を丸っと集計
        $wLast = $last > $now ? $now : $last;
        $con = $this->_storage->getConnection('statistics');
        $sql = 'SELECT sum(count) FROM daily_uu WHERE regist_date BETWEEN ? AND ?';
        $stmt = $con->prepare($sql);
        $stmt->execute(array($start,$end));
        $ret['total'] += (int)$stmt->fetchColumn();
        //device
        $sql = 'SELECT type,sum(count) as sum FROM daily_uu_device WHERE regist_date BETWEEN ? AND ? GROUP BY device';
        $stmt = $con->prepare($sql);
        $stmt->execute(array($start,$end));
        while($row = $stmt->fetch()) {
            $ret['device_'.$row['type']] += (int)$row['sum'];
        }
        //sex
        $sql = 'SELECT type,sum(count) as sum FROM daily_uu_sex WHERE regist_date BETWEEN ? AND ? GROUP BY device';
        $stmt = $con->prepare($sql);
        $stmt->execute(array($start,$end));
        while($row = $stmt->fetch()) {
            $ret['sex_'.$row['type']] += (int)$row['sum'];
        }
        
        //本日のデータが含まれるか
        if($start <= $now && $last >= $now) {
            //今日のデータ取得
            $counts = $this->getUuCount();
            foreach($ret as $key => $value) {
                $ret[$key] = isset($counts[$key]) ? $value + $counts[$key] : $value;
            }
        }
        
        return $ret;
    }
}

