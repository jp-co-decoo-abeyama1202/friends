<?php
/**
 * 日別の各種UUを集計する
 * @author admin-97
 */
class CronDailyUu extends \library\Cron
{
    public function run($time = null)
    {
        $search = strtotime(date('Y/m/d 00:00:00',strtotime('-1 day')));

        //Userテーブルからデータを引き抜く
        $dcon = $this->_storage->getConnection('default');
        $scon = $this->_storage->getConnection('statistics');
        $tableName = 'uu_' . date('Ymd',$search);
        $stmt = $scon->prepare('SELECT id FROM ' . $tableName);
        $stmt->execute();
        $ids = array();
        while($id = (int)$stmt->fetchColumn()) {
            $ids[] = $id;
        }
        if(!$ids) {
            return;
        }
        $sql = 'SELECT id,sex,device FROM user WHERE id IN ('.implode(',',$ids).')';
        $stmt = $dcon->prepare($sql);
        $stmt->execute();
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
        //SQL作成・発行
        //daily_uu
        $sql = 'INSERT INTO daily_uu (regist_date,count) VALUES (?,?)';
        $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
        $stmt = $scon->prepare($sql);
        $stmt->execute(array($search,$count));
        //daily_uu_device
        $sql = 'INSERT INTO daily_uu_device (regist_date,type,count) VALUES (?,?,?)';
        $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
        foreach($deviceData as $type => $count) {
            $stmt = $scon->prepare($sql);
            $stmt->execute(array($search,$type,$count,));
        }
        //daily_uu_sex
        $sql = 'INSERT INTO daily_uu_sex (regist_date,type,count) VALUES (?,?,?)';
        $sql.= ' ON DUPLICATE KEY UPDATE count=values(count)';
        foreach($sexData as $type => $count) {
            $stmt = $scon->prepare($sql);
            $stmt->execute(array($search,$type,$count,));
        }
    }
}
