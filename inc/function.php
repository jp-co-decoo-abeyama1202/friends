<?php
/**
 * 各種functionを作る
 */

/**
 * 配列からkeysで指定したkeyに合致するvaluesのみ取得し、その配列を戻す
 * @param array $keys 抜き出すキーの配列
 * @param array $values 抜き出される配列
 * @param int $head キーの頭につける文字列
 */
function getKeyValues($keys,$values,$head="")
{
    $ret = array();
    foreach($keys as $key) {
        $value = isset($values[$head.$key]) ? $values[$head.$key] : null;
        if(!is_null($value)) {
            $ret[$key] = $value;
        }
    }
    return $ret;
}