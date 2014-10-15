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

/**
 * フレンド用ソート用関数
 * 最近ログインしたユーザを上
 * ログイン時間が同じなら、
 * 最近フレンドになったユーザが上
 * @param type $a
 * @param type $b
 * @return int
 */
function friendsort($a,$b) {
    if($a['login_time'] === $b['login_time']) {
        if($a['create_time'] === $b['create_time']) {
            return 0;
        }
        return $a['create_time'] > $b['create_time'] ? -1 : 1;
    }
    return $a['login_time'] > $b['login_time'] ? -1 : 1;
}

function groupsort($a,$b) {
    return $a['join_time'] > $b['join_time'] ? -1 : 1;
}

/**
 * トークタブ用ソート関数
 */
function newlistsort($a,$b)
{
    return $a['update_time'] > $b['update_time'] ? -1 : 1;
}

/**
 * 
 */
function groupUserSort($a,$b)
{
    return $a['create_time'] < $b['create_time'] ? -1 : 1;
}

