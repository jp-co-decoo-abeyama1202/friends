<?php
require_once(__DIR__ . '/../_header.php');
$storage->beginTransaction();
try{
    $storage->Group->create(1,'テストグループ','',array(1,2,3));
} catch(\Exception $e) {
    error_log($e);
    $storage->rollback();
}
$storage->commit();