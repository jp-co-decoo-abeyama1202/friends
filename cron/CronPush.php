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
class CronPush extends \library\Cron
{
    public function run($args = array())
    {
        $this->_storage->PushQueue->send();
    }
}
