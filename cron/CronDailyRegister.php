<?php
/**
 * 日別の登録者数を集計する。
 * @author admin-97
 */
class CronDailyRegister extends \library\Cron
{
    public function run($args = array())
    {
        $searchF = strtotime(date('Y/m/d 00:00:00',strtotime('-1 day')));
        $searchT = strtotime(date('Y/m/d 23:59:59',strtotime('-1 day')));
        if(isset($args[0]) && isset($args[1])) {
            $searchF = is_numeric($args[0]) ? $args[0] : strtotime($args[0]);
            $searchT = is_numeric($args[1]) ? $args[1] : strtotime($args[1]);
        }
        //Userテーブルからデータを引き抜く
        $con = $this->_storage->getConnection('default');
        $sql = 'SELECT sex,device FROM user WHERE create_time BETWEEN ? AND ?';
        $stmt = $con->prepare($sql);
        $stmt->execute(array($searchF,$searchT));
        $count = 0;
        $deviceData = array(
            \library\Model_User::DEVICE_IOS => 0,
            \library\Model_User::DEVICE_ANDROID => 0,
        );
        $sexData = array(
            \library\Model_User::SEX_ALL => 0,
            \library\Model_User::SEX_MAN => 0,
            \library\Model_User::SEX_WOMAN => 0,
        );
        while($row = $stmt->fetch()) {
            $device = (int)$row['device'];
            $sex = (int)$row['sex'];
            $count++;
            $deviceData[$device]++;
            $sexData[$sex]++;
        }
        //登録・更新
        $scon = $this->_storage->getConnection("statistics");
        //daily_uu
        $sql = 'INSERT INTO daily_register (regist_date,count) VALUES (?,?)';
        $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
        $stmt = $scon->prepare($sql);
        $stmt->execute(array($searchF,$count));
        //daily_uu_device
        $sql = 'INSERT INTO daily_register_device (regist_date,type,count) VALUES (?,?,?)';
        $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
        foreach($deviceData as $type => $count) {
            $stmt = $scon->prepare($sql);
            $stmt->execute(array($searchF,$type,$count,));
        }
        //daily_uu_sex
        $sql = 'INSERT INTO daily_register_sex (regist_date,type,count) VALUES (?,?,?)';
        $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
        foreach($sexData as $type => $count) {
            $stmt = $scon->prepare($sql);
            $stmt->execute(array($searchF,$type,$count,));
        }
    }
}
