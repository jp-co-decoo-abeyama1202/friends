<?php
$last = exec('timeout -s 1 5 tail -f /var/log/php-fpm/web.error.log',$output);
var_dump($last);
var_dump($output);
?>
