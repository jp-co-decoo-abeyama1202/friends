<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Cron_Test
 *
 * @author admin-97
 */
class Cron_Test extends \library\Cron
{
    public function run($args = array())
    {
        $user = $this->_storage->User->primaryOne(4);
        foreach($user as $key => $v) {
            echo $key . ":" . $v."\n";
        }
    }
}
