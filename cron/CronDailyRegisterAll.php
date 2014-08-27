<?php
/**
 * 全ての登録者数を集計する。
 * とりあえず叩いとけば再集計も出来ちゃう。
 * @author admin-97
 */
class CronDailyRegisterAll extends \library\Cron
{
    public function run()
    {
        //Userテーブルからデータを引き抜く
        $con = $this->_storage->getConnection('default');
        $sql = 'SELECT id,sex,device,create_time FROM user';
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $counts = array();
        $deviceList = array();
        $sexList = array();
        //集計
        while($row = $stmt->fetch()) {
            $date = strtotime(date('Y/m/d',$row['create_time']));
            if(!array_key_exists($date,$counts)) {
                $counts[$date] = 0;
                $deviceList[$date] = array(
                    \library\Model_User::DEVICE_IOS => 0,
                    \library\Model_User::DEVICE_ANDROID => 0,
                );
                $sexList[$date] = array(
                    \library\Model_User::SEX_ALL => 0,
                    \library\Model_User::SEX_MAN => 0,
                    \library\Model_User::SEX_WOMAN => 0,
                );
            }
            $device = (int)$row['device'];
            $sex = (int)$row['sex'];
            $counts[$date]++;
            $deviceList[$date][$device]++;
            $sexList[$date][$sex]++;
        }
        //SQL作成・発行
        $scon = $this->_storage->getConnection('statistics');
        //daily_register
        $values = array();
        foreach($counts as $date => $count) {
            $values[] = '('.$date.','.$count.')';
        }
        if($values) {
            $sql = 'INSERT INTO daily_register (regist_date,count) VALUES ' . implode(',',$values);
            $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
            $stmt = $scon->prepare($sql);
            $stmt->execute();
        }
        //daily_register_device
        $values = array();
        foreach($deviceList as $date => $counts) {
            $values[] = '('.$date.','.\library\Model_User::DEVICE_IOS.','.$counts[\library\Model_User::DEVICE_IOS].')';
            $values[] = '('.$date.','.\library\Model_User::DEVICE_ANDROID.','.$counts[\library\Model_User::DEVICE_ANDROID].')';
        }
        if($values) {
            $sql = 'INSERT INTO daily_register_device (regist_date,type,count) VALUES ' . implode(',',$values);
            $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
            $stmt = $scon->prepare($sql);
            $stmt->execute();
        }
        //daily_register_sex
        $values = array();
        foreach($sexList as $date => $counts) {
            $values[] = '('.$date.','.\library\Model_User::SEX_ALL.','.$counts[\library\Model_User::SEX_ALL].')';
            $values[] = '('.$date.','.\library\Model_User::SEX_MAN.','.$counts[\library\Model_User::SEX_MAN].')';
            $values[] = '('.$date.','.\library\Model_User::SEX_WOMAN.','.$counts[\library\Model_User::SEX_WOMAN].')';
        }
        if($values) {
            $sql = 'INSERT INTO daily_register_sex (regist_date,type,count) VALUES ' . implode(',',$values);
            $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
            $stmt = $scon->prepare($sql);
            $stmt->execute();
        }
    }
}
