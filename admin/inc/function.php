<?php
/**
 * 各種functionを作る
 */
function redirect($url)
{
    header("Location:".$url);
    exit;
}

function escapetext($value)
{
    $ret = \htmlspecialchars($value, \ENT_QUOTES);
    return $ret;
}

function icon($value,$type = 'sex') {
    $func = 'icon_'.$type;
    return $func($value);
}
/**
 * 性別表示用
 * @param type $value
 * @return string
 */
function icon_sex($value)
{
    if($value == \library\Model_User::SEX_MAN) {
        return '<button class="btn btn-info btn-sm"><i class="ion ion-male"> 男性</icon></button>';
    } else if($value == \library\Model_User::SEX_WOMAN) {
        return '<button class="btn btn-danger btn-sm"><i class="ion ion-female"> 女性</icon></button>';
    } else {
        return '<button class="btn btn-success btn-sm"><i class="ion ion-help">指定なし</icon></button>';
    }
}

/**
 * 
 * @param type $value
 * @return string
 */
function icon_request($value)
{
    if($value == \library\Model_User::REQUEST_VALID) {
        return '<button class="btn btn-info btn-sm">許可</button>';
    } else {
        return '<button class="btn btn-danger btn-sm">不許可</button>';
    }
}

function icon_publishing($value)
{
    if($value == \library\Model_User::PUBLISHING_VALID) {
        return '<button class="btn btn-success btn-sm">全体公開</button>';
    } else {
        return '<button class="btn btn-danger btn-sm">フレンドまでに公開</button>';
    }
}

function icon_device($value)
{
    if($value == \library\Model_User::DEVICE_IOS) {
        return '<button class="btn btn-ios btn-sm"><i class="ion ion-social-apple"> iOS</icon></button>';
    } else {
        return '<button class="btn btn-android btn-sm"><i class="ion ion-social-android"> Android</icon></button>';
    }
}

function icon_state($value)
{
    if($value == \library\Model_User::STATE_VALID) {
        return '<div class="badge bg-green">通常</div>';
    } else {
        return '<div class="badge bg-red">削除</div>';
    }
}

function icon_request_state($value)
{
    switch($value) {
        case \library\Model_UserRequestFrom::STATE_PENDING:
            return '<div class="badge bg-blue">申請</div>';
        case \library\Model_UserRequestFrom::STATE_EXECUTE:
            return '<div class="badge bg-green">成立</div>';
        case \library\Model_UserRequestFrom::STATE_REFUSE:
            return '<div class="badge bg-red">拒否</div>';
        case \library\Model_UserRequestFrom::STATE_CANCELL:
            return '<div class="badge bg-yellow">取り消し</div>';
        default:
            return '';
    }
}

function icon_delete_flag($value)
{
    switch($value) {
        case \library\Model_UserRequestFrom::DELETE_ON:
            return '<div class="badge bg-red">削除</div>';
        default:
            return '<div class="badge bg-aqua">表示</div>';
    }
}

function icon_read_flag($value)
{
    switch($value) {
        case \library\Model_Message::READ_ON:
            return '<div class="badge bg-green">既読</div>';
        default:
            return '<div class="badge bg-red">未読</div>';
    }
}

function mergeValue()
{
    $count = func_num_args();
    $ids = array();
    for($i=0;$i<$count;++$i) {
        $list = func_get_arg($i);
        if(!is_array($list)) {
            continue;
        }
        while(list($key,$value) = each($list)) {
            if(!in_array($value,$ids)) {
                $ids[] = $value;
            }
        }
    }
    return $ids;
}

/**
 * Boxに入ったテーブルを出力する
 * $columns = array(
 *     'box_id' => 'div.box に指定するID',
 *     'box_class' => 'div.box に追加するclass',
 *     'table_id' => 'tableに指定するID',
 *     'table_icon' => 'tableのタイトル左に表示するアイコン',
 *     'table_title' => 'tableのタイトル',
 *     'table_header' => 'theadとtfootの内容',
 *     'table_data' => 'tbodyの内容',
 *     'error_message' => 'tableDataが空だった場合の表示メッセージ'
 * );
 * 'table_header&table_data' => array(
 *     $value, // 標準の形。htmlescapeされて表示される文字列
 *     array(
 *         'params' => 'tdに追加するclassやstyle等のパラメータ文字列',
 *         'escape' => '表示する文字をエスケープするか。リンクを表示する場合などはfalse指定',
 *         'value' => '表示内容'
 *     ),
 * );
 * @param type $columns
 */
function createBoxTable($columns)
{
    $_boxId = isset($columns['box_id']) ? $columns['box_id'] : '';
    $_boxClass = isset($columns['box_class']) ? $columns['box_class'] : '';
    $_tableId = isset($columns['table_id']) ? $columns['table_id'] : '';
    $_tableIcon = isset($columns['table_icon']) ? $columns['table_icon'] : '';
    $_tableTitle = isset($columns['table_title']) ? $columns['table_title'] : '';
    $_tableHeader = isset($columns['table_header']) ? $columns['table_header'] : array();
    $_tableData = isset($columns['table_data']) ? $columns['table_data'] : array();
    $_errorMessage = isset($columns['error_message']) ? $columns['error_message'] : '';
    require __DIR__ . '/_boxtable.php';
}

function bantime($start,$end)
{
    $start = (int)$start;
    $end = (int)$end;
    if(!$start && !$end) {
        return '無期限';
    }
    $ret = "";
    if($start) {
       $ret .= date('y/m/d H:i:s',$start); 
    }
    $ret .=" ～ ";
    if($end) {
        $ret .= date('y/m/d H:i:s',$end);
    }
    return escapetext($ret);
}

function banstatus($available,$start,$end)
{
    if($available == \library\Model_UserBan::AVAILABLE_FALSE) {
        return '無効';
    }
    $now = time();
    if($start > $now) {
        return '予約中';
    }
    return '有効';
}

function getWeek($time)
{
    $week = \date('N',$time);
    $weeks = array(
        1 => '月',
        2 => '火',
        3 => '水',
        4 => '木',
        5 => '金',
        6 => '土',
        7 => '日',
    );
    return $weeks[$week];
}