<?php
require_once(__DIR__ . '/../_header.php');
$list = array(
    array('update_time' => 1),
    array('update_time' => 2),
);
usort($list,'newlistsort');
var_dump($list);