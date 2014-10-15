<?php
require_once('/home/homepage/html/public/friends/admin/inc/define.php');
?>
<?php
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$friendId = isset($_GET['friend_id']) ? (int)$_GET['friend_id'] : null;
$friendsId = isset($_GET['friends_id']) ? (int)$_GET['friends_id'] : null;
if(!$userId || (!$friendId && !$friendsId)) {
    return redirect(\library\Assets::uri('data/index.php'));
}
if($friendsId) {
    if(!$friendId) {
        //friendsIdからフレンド情報取得
        $friends = $storage->Friends->primaryOne($friendsId);
        if(!$friends) {
            return redirect(\library\Assets::uri('data/index.php'));
        }
        if($friends['user_id_1'] == $userId) {
            $friendId = (int)$friends['user_id_2'];
        } else {
            $friendId = (int)$friends['user_id_1'];
        }
    }
} else {
    $friendsId = $storage->Friends->getId($userId,$friendId);
}

$users = $storage->User->primary(array($userId,$friendId));
if(count($users) !== 2) {
    //おかしいなぁ
    return redirect(\library\Assets::uri('data/index.php'));
}
$tokens = $storage->UserToken->getTokens(array($userId,$friendId));
$messages = array();
$messageTableData = array();
if($friendsId) {
    $messages = $storage->Message->getAllMessage($friendsId);
}
foreach($messages as $id => $message) {
    $w = array();
    $w[] = $message['id'];
    if($message['sender_id'] == $userId) {
        $w[] = array(
            'params' => 'style="background-color:yellow;font-weight:bold;"',
            'value' => $userId,
        );
        $w[] = $friendId;
    } else {
        $w[] = $friendId;
        $w[] = array(
            'params' => 'style="background-color:yellow;font-weight:bold;"',
            'value' => $userId,
        );
    }
    $w[] = $message['message'];
    $w[] = array(
        'params' => 'style="text-align:center;"',
        'escape' => false,
        'value' => icon($message['read_flag'],'read_flag'),
    );
    $w[] = array(
        'params' => 'style="text-align:center;"',
        'escape' => false,
        'value' => icon($message['delete_flag'],'delete_flag'),
    );
    $w[] = date('y/m/d H:i:s',$message['create_time']);
    $w[] = $message['create_time'] == $message['update_time'] ? '-' :date('y/m/d H:i:s',$message['update_time']);
    $messageTableData[] = $w;
}
$messageTableContents = array(
    'box_class' => 'box-info',
    'table_id' => 'message_list',
    'table_icon' => 'ion ion-ios7-chatboxes-outline',
    'table_title' => '会話ログ',
    'table_header' => array('mid','送信側','受信側','メッセージ','既読','状態','送信日時','既読日時'),
    'table_data' => $messageTableData,
    'error_message' => '会話ログが1件もありません',
);
//ユーザ間の関係を取得
$relation = $storage->User->getRelation($userId,$friendId);

//HTMLスタート
$_cssList = array(
    'datatables/dataTables.bootstrap.css',
);
require_once(__DIR__ . '/../_header.php');
?>
<style>
th,td{vertical-align:middle!important;}
</style>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>会話ログ</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-person"> ユーザ情報</i></h3>
                    </div>
                    <div class="box-body table-responsive">
                        <div class="box box-solid bg-yellow">
                            <div class="box-header"><h3 class="box-title">ID:<?=$userId?> → ID:<?=$friendId?> 間の関係：<span style="font-weight:bold;"><?=\library\admin\Model_User::$_relationList[$relation]?></span></h3></div>
                        </div>
                        <table id="user_list" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>IDトークン</th>
                                    <th>名前</th>
                                    <th>状態</th>
                                    <th>国 / 地域</th>
                                    <th>登録日</th>
                                    <th>最終ログイン</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $id => $row):
                                    $style = $id === $userId ? 'style="background-color:yellow;font-weight:bold;"' : '';
                                ?>
                                <tr>
                                    <td><a href="<?=\library\Assets::uri('data/detail.php?id='.$row['id'])?>"><button class="btn btn-sm btn-info">詳細</button></a></td>
                                    <td <?=$style?>><?=$row['id']?></td>
                                    <td><?=$tokens[$id]?></td>
                                    <td><?=$row['name']?></td>
                                    <td><?=icon($row['state'],'state')?></td>
                                    <td><?=\library\admin\Model_User::$_countryList[$row['country']]?> / <?=$row['area']?></td>
                                    <td><?=date('Y/m/d H:i:s',$row['create_time'])?></td>
                                    <td><?=date('Y/m/d H:i:s',$row['login_time'])?></td>
                                </tr>
                                <?php endforeach?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?php if(!$friendsId):?>
                <div class="box-body table-responsive">
                    <div class="alert alert-danger alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <b>フレンドであった形跡がありません</b>
                    </div>
                </div>    
                <?php else:?>
                <div class="box-body table-responsive">
                    <?=createBoxTable($messageTableContents);?>
                </div>
                <?php endif?>
            </div>
        </div>
    </section>
</aside>
<?php 
$_jsList = array(
    'plugins/datatables/jquery.dataTables.js',
    'plugins/datatables/dataTables.bootstrap.js',
);
require_once('../_footContent.php');
?>
<?php
    /*
     * dataTablesを使ってフレンド一覧、ブロック一覧を制御する。
     * 重くなったらAPI取得やら遅延表示処理やらを考慮
     */
?>
<script type="text/javascript">
var user_id = <?=$userId?>;
var noimage = '<?=\library\Assets::uri('noimage.png','img')?>';
$(function() {
    var tableOption = {
        "bPaginate": true,
        "bLengthChange": false,
        "bFilter": true,
        "bSort": false,
        "bInfo": true,
        "bAutoWidth": false,
        "aaSorting":[[4,'desc']],
    };
    $('#message_list').dataTable(tableOption);
});
</script>
<?php
require_once('../_footer.php');