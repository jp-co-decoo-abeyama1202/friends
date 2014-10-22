<?php
require_once('/home/homepage/html/public/friends/admin/inc/define.php');
$groupId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$userId = isset($_GET['uid']) ? (int)$_GET['uid'] : null;
if(!$groupId) {
    return redirect(\library\Assets::uri('data/group/index.php'));
}
//Group取得
$group = $storage->Group->primaryOne($groupId);
//GroupUser取得
$users = $storage->GroupUser->getMemberList($groupId);
//会話ログ取得
$messages = $storage->GroupMessage->getAllMessage($groupId);
$tokens = $storage->UserToken->getTokens(array($userId,$friendId));
$messages = array();
$messageTableData = array();
foreach($messages as $id => $message) {
    $w = array();
    $w[] = $message['id'];
    if($message['sender_id'] == $userId) {
        $w[] = array(
            'params' => 'style="background-color:yellow;font-weight:bold;"',
            'value' => $userId,
        );
    } else {
        $w[] = $message['sender_id'];
    }
    $w[] = $message['message'];
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
    'table_title' => 'グループ会話ログ',
    'table_header' => array('mid','送信側','受信側','メッセージ','既読','状態','送信日時','既読日時'),
    'table_data' => $messageTableData,
    'error_message' => '会話ログが1件もありません',
);
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
        <h1>グループ会話ログ</h1>
    </section>
    <section class="content">
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
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-person"> グループメンバー情報</i></h3>
                    </div>
                    <div class="box-body table-responsive">
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