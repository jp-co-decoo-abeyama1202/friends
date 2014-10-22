<?php
require_once('/home/homepage/html/public/friends/admin/inc/define.php');
?>
<?php
$userId = isset($_GET['id']) ? $_GET['id'] : null;
if(!$userId) {
    return redirect(\library\Assets::uri('data/index.php'));
}
if(!is_numeric($userId)) {
    //トークンの可能性
    $user = $storage->User->getDataFromToken($userId);
    if($user) {
        //数値データでリダイレクト
        return redirect(\library\Assets::uri('data/detail.php?id='.$user['id']));
    } else {
        //トークンですらない
        return redirect(\library\Assets::uri('data/index.php'));
    }
}
$userId = (int)$userId;
$data = $storage->User->get($userId);
$user = $data['user'];
if(!$user['token']) {
    $user = null;
}
if($user) {
    $option = $data['option'];
    $photos = $data['photos'];
    $comments = $data['comments'];
    $friends = $storage->UserFriend->getFriendsAll($userId);
    $blocks = $storage->UserBlock->getBlocks($userId);
    $blockers = $storage->UserBlocker->getBlockers($userId);
    $requestFrom = $storage->UserRequestFrom->getRequests($userId);
    $requestTo = $storage->UserRequestTo->getRequests($userId);
    $ids = mergeValue(array_keys($friends),array_keys($blocks),array_keys($blockers),array_keys($requestFrom),array_keys($requestTo));
    $users = $storage->User->primary($ids);
    $tokens = $storage->UserToken->getTokens($ids);
    $memo = $storage->UserMemo->primaryOne($userId);
    $ban = $storage->UserBan->primaryOne($userId);
    $banH = $storage->UserBanHistory->getList($userId);
    //フレンド一覧表示
    $friendTableData = array();
    foreach($friends as $id => $friend) {
        $detailUrl = \library\Assets::uri('data/detail.php?id='.$id);
        $chatUrl = \library\Assets::uri('data/message.php?id='.$userId.'&friend_id='.$id);
        $w = array();
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $detailUrl . '"><button class="btn btn-sm btn-info">詳細</button></a>',
        );
        $w[] = $id;
        $w[] = $tokens[$id];
        $w[] = $users[$id]['name'];
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($users[$id]['state'],'state'),
        );
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($users[$id]['device'],'device'),
        );
        $w[] = date('Y/m/d H:i:s',$friend['create_time']);
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $chatUrl . '"><button class="btn btn-sm btn-success">　閲　覧　</button></a>',
        );
        $friendTableData[] = $w;
    }
    $friendTableContents = array(
        'box_id' => 'tab-friend',
        'box_class' => 'tab-pane active box-danger',
        'table_id' => 'friend_list',
        'table_icon' => 'ion ion-person-stalker',
        'table_title' => 'フレンド一覧',
        'table_header' => array('','ID','IDトークン','名前','状態','デバイス','フレンド成立日時','会話ログ'),
        'table_data' => $friendTableData,
        'error_message' => 'フレンドが1人もいません',
    );
    //ブロック一覧表示
    $blockTableData = array();
    foreach($blocks as $id => $block) {
        $detailUrl = \library\Assets::uri('data/detail.php?id='.$id);
        $chatUrl = \library\Assets::uri('data/message.php?id='.$userId.'&friend_id='.$id);
        $w = array();
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $detailUrl . '"><button class="btn btn-sm btn-info">詳細</button></a>',
        );
        $w[] = $id;
        $w[] = $tokens[$id];
        $w[] = $users[$id]['name'];
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($users[$id]['state'],'state'),
        );
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($users[$id]['device'],'device'),
        );
        $w[] = date('Y/m/d H:i:s',$block['create_time']);
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $chatUrl . '"><button class="btn btn-sm btn-success">　閲　覧　</button></a>',
        );
        $blockTableData[] = $w;
    }
    $blockTableContents = array(
        'box_id' => 'tab-block',
        'box_class' => 'tab-pane box-danger',
        'table_id' => 'block_list',
        'table_icon' => 'ion ion-ios7-close',
        'table_title' => 'ブロック一覧',
        'table_header' => array('','ID','IDトークン','名前','状態','デバイス','ブロック日時','会話ログ'),
        'table_data' => $blockTableData,
        'error_message' => '1人もブロックしていません',
    );
    //ブロッカー一覧表示
    $blockerTableData = array();
    foreach($blockers as $id => $blocker) {
        $detailUrl = \library\Assets::uri('data/detail.php?id='.$id);
        $chatUrl = \library\Assets::uri('data/message.php?id='.$userId.'&friend_id='.$id);
        $w = array();
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $detailUrl . '"><button class="btn btn-sm btn-info">詳細</button></a>',
        );
        $w[] = $id;
        $w[] = $tokens[$id];
        $w[] = $users[$id]['name'];
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($users[$id]['state'],'state'),
        );
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($users[$id]['device'],'device'),
        );
        $w[] = date('Y/m/d H:i:s',$blocker['create_time']);
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $chatUrl . '"><button class="btn btn-sm btn-success">　閲　覧　</button></a>',
        );
        $blockerTableData[] = $w;
    }
    $blockerTableContents = array(
        'box_id' => 'tab-blocker',
        'box_class' => 'tab-pane box-danger',
        'table_id' => 'blocker_list',
        'table_icon' => 'ion ion-close',
        'table_title' => 'ブロッカー一覧',
        'table_header' => array('','ID','IDトークン','名前','状態','デバイス','ブロック日時','会話ログ'),
        'table_data' => $blockerTableData,
        'error_message' => '誰からもブロックされていません',
    );
    //送信一覧表示
    $fromTableData = array();
    foreach($requestFrom as $id => $from) {
        $detailUrl = \library\Assets::uri('data/detail.php?id='.$id);
        $requestUrl = \library\Assets::uri('data/request.php?id='.$userId.'&friend_id='.$id);
        $chatUrl = \library\Assets::uri('data/message.php?id='.$userId.'&friend_id='.$id);
        $w = array();
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $detailUrl . '"><button class="btn btn-sm btn-info">詳細</button></a>',
        );
        $w[] = $id;
        $w[] = $tokens[$id];
        $w[] = $users[$id]['name'];
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($from['state'],'request_state'),
        );
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($from['delete_flag'],'delete_flag'),
        );
        $w[] = date('Y/m/d H:i:s',$from['update_time']);
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $requestUrl . '"><button class="btn btn-sm btn-success">　閲　覧　</button></a>',
        );
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $chatUrl . '"><button class="btn btn-sm btn-success">　閲　覧　</button></a>',
        );
        $fromTableData[] = $w;
    }
    $fromTableContents = array(
        'box_id' => 'tab-from',
        'box_class' => 'tab-pane box-danger',
        'table_id' => 'from_list',
        'table_icon' => 'ion ion-person-add',
        'table_title' => 'リクエスト送信一覧',
        'table_header' => array('','ID','IDトークン','名前','申請状況','表示','最終更新日時','リク内容','会話ログ'),
        'table_data' => $fromTableData,
        'error_message' => '1件も送信していません',
    );
    //受信一覧表示
    $toTableData = array();
    foreach($requestTo as $id => $to) {
        $detailUrl = \library\Assets::uri('data/detail.php?id='.$id);
        $requestUrl = \library\Assets::uri('data/request.php?id='.$id.'&friend_id='.$userId);
        $chatUrl = \library\Assets::uri('data/message.php?id='.$userId.'&friend_id='.$id);
        $w = array();
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $detailUrl . '"><button class="btn btn-sm btn-info">詳細</button></a>',
        );
        $w[] = $id;
        $w[] = $tokens[$id];
        $w[] = $users[$id]['name'];
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($to['state'],'request_state'),
        );
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => icon($to['delete_flag'],'delete_flag'),
        );
        $w[] = date('Y/m/d H:i:s',$to['update_time']);
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $requestUrl . '"><button class="btn btn-sm btn-success">　閲　覧　</button></a>',
        );
        $w[] = array(
            'params' => 'class="text-center"',
            'escape' => false,
            'value' => '<a href="' . $chatUrl . '"><button class="btn btn-sm btn-success">　閲　覧　</button></a>',
        );
        $toTableData[] = $w;
    }
    $toTableContents = array(
        'box_id' => 'tab-to',
        'box_class' => 'tab-pane box-danger',
        'table_id' => 'to_list',
        'table_icon' => 'ion ion-android-inbox',
        'table_title' => 'リクエスト受信一覧',
        'table_header' => array('','ID','IDトークン','名前','申請状況','表示','最終更新日時','リク内容','会話ログ'),
        'table_data' => $toTableData,
        'error_message' => '1件も受信していません',
    );
}
//HTMLスタート
$_cssList = array(
    'datepicker/datepicker3.css',
    'datatables/dataTables.bootstrap.css',
    '/daterangepicker/daterangepicker-bs3.css'
);
require_once(__DIR__ . '/../_header.php');
?>
<style>
th,td{vertical-align:middle!important;}
.form-control[readonly]{background-color:white;}
</style>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>ユーザ詳細</h1>
    </section>
    <section class="content">
        <?php if($user):?>
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-danger">
                    <div class="box-header bg-red">
                        <h3 class="box-title"><i class="ion ion-checkmark-circled"> 管理者操作</i></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-sm" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <th class="text-center" style="width:88px;">管理用メモ</th>
                                <td>
                                    <textarea class="form-control" id="user_memo"><?=escapetext($memo['memo'])?></textarea>
                                </td>
                                <td style="width:30px;">
                                    <div class="margin-3"><button class="btn btn-success" id="user_memo_update">更新</button></div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center bg-red">BAN</th>
                                <td>
                                    <form method="POST" role="form" id="ban_form">
                                        <table class="table">
                                            <tr>
                                                <th style="width:50px;">理由</th>
                                                <td><textarea class="form-control" id="ban_reason" name="ban_reason"></textarea></td>
                                            </tr>
                                            <tr>
                                                <th>期間</th>
                                                <td>
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-clock-o"></i>
                                                        </div>
                                                        <input type="text" class="form-control pull-right" id="ban_time"/>
                                                    </div><!-- /.input group -->
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                </td>
                                <td>
                                    <div class="margin-3"><button class="btn btn-info" id="ban_add">登録</button></div>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-center">BAN履歴</th>
                                <td colspan="2">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>理由</th>
                                                <th>期間</th>
                                                <th>ステータス</th>
                                                <th>操作</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if($ban):?>
                                            <tr>
                                                <td class="text-center" style="width:70px;">-</td>
                                                <td><?=escapetext($ban['reason'])?></td>
                                                <td style="width:100px;"><?=bantime($ban['start_time'],$ban['end_time'])?></td>
                                                <td style="width:85px;"><?=banstatus($ban['available'],$ban['start_time'],$ban['end_time'])?></td>
                                                <td style="width:30px;">
                                                    <?php if($ban['available'] == \library\Model_UserBan::AVAILABLE_TRUE):?>
                                                        <div class="margin-3"><button class="btn btn-danger" id="ban_off">無効化</button></div>
                                                    <?php else:?>
                                                        <div class="margin-3"><button class="btn btn-success" id="ban_on">有効化</button></div>
                                                    <?php endif?>
                                                </td>
                                            </tr>
                                            <?php endif?>
                                            <?php foreach($banH as $ban):?>
                                            <tr>
                                                <td class="text-right" style="width:70px;"><?=$ban['id']?></td>
                                                <td><?=escapetext($ban['reason'])?></td>
                                                <td style="width:100px;"><?=bantime($ban['start_time'],$ban['end_time'])?></td>
                                                <td class="text-center" style="width:85px;">-</td>
                                                <td class="text-center" style="width:30px;">-</td>
                                            </tr>
                                            <?php endforeach?>
                                        </tbody>
                                    </table>
                                </td>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-person"> 基本情報</i></h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <td rowspan="5" colspan="2" class="text-center" style="width:210px;">
                                    <?php
                                        $_id = $user['image'] ? 'img_image' : '';
                                        $_src = $user['image'] ? convertImg($user['image']) : \library\Assets::uri('noimage.png','img');
                                    ?>
                                    <img <?=$_id ? 'id="'.$_id.'"' : ''?> src="<?=$_src?>" height="200px" width="200px" class="margin-5 deletable"/>
                                </td>
                                <th>ID : トークン</th>
                                <td><?=$user['id']?> : <?=$user['token']?></td>
                            </tr>
                            <tr>
                                <th>名前</th>
                                <td colspan="3"><?=  escapetext($user['name'])?> <?= icon($user['state'],'state')?></td>
                            </tr>
                            <tr>
                                <th>性別</th>
                                <td><?= icon_sex($user['sex'])?></td>
                            </tr>
                            <tr>
                                <th>年齢</th>
                                <td><?= \library\admin\Model_User::$_ageList[$user['age']]?></td>
                            </tr>
                            <tr>
                                <th>国 / 地域</th>
                                <td><?= \library\admin\Model_User::$_countryList[$user['country']]?> / <?= $user['area'] ? $user['area'] : '指定なし'?></td>
                            </tr>
                            <tr>
                                <th>登録日</th>
                                <td><?= date('Y/m/d H:i:s',$user['create_time'])?></td>
                                <th>フレンドリクエスト</th>
                                <td><?= icon($user['request'],'request')?></td>
                            </tr>
                            <tr>
                                <th>更新日</th>
                                <td><?= date('Y/m/d H:i:s',$user['update_time'])?></td>
                                <th>公開範囲</th>
                                <td><?= icon($user['publishing'],'publishing')?></td>
                            </tr>
                            <tr>
                                <th>最終ログイン</th>
                                <td><?= date('Y/m/d H:i:s',$user['login_time'])?></td>
                                <th>デバイス</th>
                                <td><?= icon($user['device'],'device')?></td>
                            </tr>
                            <tr>
                                <th>プロフィール</th>
                                <td colspan="3">
                                    <textarea class="form-control" readonly><?= escapetext($user['profile'])?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <th rowspan="3">PUSH通知</th>
                                <th>PUSH_ID</th>
                                <td colspan="2"><?=$user['push_id']?></td>
                            </tr>
                            <tr>
                                <th>友だちリクエスト受信時</th>
                                <td colspan="2"><?=$option['push_friend'] == \library\Model_UserOption::FLAG_ON ? '受信する' : '受信しない' ?></td>
                            </tr>
                            <tr>
                                <th>チャット受信時</th>
                                <td colspan="2"><?=$option['push_chat'] == \library\Model_UserOption::FLAG_ON ? '受信する' : '受信しない' ?></td>
                            </tr>
                            <tr>
                                <th rowspan="6">その他情報</th>
                                <th>タイトル</th>
                                <th colspan="2">本文</th>
                            </tr>
                            <?php for($i=1;$i<=5;++$i):?>
                            <tr>
                                <td><textarea class="form-control" readonly><?= isset($comments['comment_'.$i]) ? escapetext($comments['comment_'.$i]['title']) : '　' ?></textarea></td>
                                <td colspan="2">
                                    <textarea class="form-control" readonly><?= isset($comments['comment_'.$i]) ? escapetext($comments['comment_'.$i]['text']) : '　' ?></textarea>
                                </td>
                            </tr>
                            <?php endfor?>
                        </table>
                    </div>
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-camera"> 写真</i></h3>
                    </div>
                    <div class="box-body" id="list-photo">
                        <?php for($i=1;$i<=8;$i++):?>
                        <?php
                            $_id = isset($photos['photo_'.$i]) ? 'img_photo_' . $photos['photo_'.$i]['no'] : '';
                            $_src = isset($photos['photo_'.$i]) ? convertImg($photos['photo_'.$i]['image']) : \library\Assets::uri('noimage.png','img');
                        ?>
                        <img <?=$_id ? 'id="'.$_id.'"' : ''?> src="<?=$_src?>" width="100px" height="100px" class="margin-3 deletable"/>
                        <?php endfor?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-success">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> フレンド検索条件</i></h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>性別</th>
                                <td><?= icon_sex($option['sex'])?></td>
                                <th>国 / 地域</th>
                                <td><?= \library\admin\Model_User::$_countryList[$option['country']]?> / <?= $option['area'] ? $option['area'] : '指定なし'?></td>
                            </tr>
                            <tr>
                                <th>年齢</th>
                                <td colspan="3">
                                    <?= \library\admin\Model_User::$_ageList[$option['min_age']]?>&nbsp;～&nbsp;<?= \library\admin\Model_User::$_ageList[$option['max_age']]?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs pull-right">
                        <li><a href="#tab-blocker" data-toggle="tab">ブロッカー</a></li>
                        <li><a href="#tab-block" data-toggle="tab">ブロック</a></li>
                        <li><a href="#tab-to" data-toggle="tab">受信</a></li>
                        <li><a href="#tab-from" data-toggle="tab">送信</a></li>
                        <li class="active"><a href="#tab-friend" data-toggle="tab">フレンド</a></li>
                        <li class="pull-left header"><i class="fa fa-th"></i> 各種一覧</li>
                    </ul>
                    <div class="tab-content">
                        <?=createBoxTable($friendTableContents);?>
                        <?=createBoxTable($blockTableContents);?>
                        <?=createBoxTable($blockerTableContents);?>
                        <?=createBoxTable($fromTableContents);?>
                        <?=createBoxTable($toTableContents);?>
                    </div>
                </div>
            </div>
        </div>
        <?php else:?>
        <div class="alert alert-warning alert-dismissable">
            <i class="fa fa-warning"></i>
            <b>注意！</b> 指定されたユーザは存在しません.<br/>
            <div class="margin-5"><a href="<?=\library\Assets::uri('data/index.php')?>"> &rArr;ユーザ検索へ戻る</a></div>
        </div>
        <?php endif?>
    </section>
</aside>
<?php 
$_jsList = array(
    'plugins/input-mask/jquery.inputmask.js',
    'plugins/input-mask/jquery.inputmask.date.extensions.js',
    'plugins/input-mask/jquery.inputmask.extensions.js',
    'plugins/datepicker/bootstrap-datepicker.js',
    'plugins/datepicker/locales/bootstrap-datepicker.ja.js',
    'plugins/daterangepicker/daterangepicker.js',
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
    /* 最初から非表示な分を調整 */
    $('#ban_time').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'YYYY/MM/DD HH:mm:ss'});
    var tableOption = {
        "bPaginate": true,
        "bLengthChange": false,
        "bFilter": true,
        "bSort": true,
        "bInfo": true,
        "bAutoWidth": false,
        "aaSorting":[[1,'asc']],
    };
    $('#friend_list').dataTable(tableOption);
    $('#block_list').dataTable(tableOption);
    $('#blocker_list').dataTable(tableOption);
    $('#from_list').dataTable(tableOption);
    $('#to_list').dataTable(tableOption);
    $('img.deletable').click(function(){
        var img_data = $(this).attr('id');
        if(!img_data) return;
        
        if(confirm('この画像を削除しますか？削除した画像は復旧できません。')) {
            var params = {
                'user_id':user_id,
                'img_data':img_data
            };
            params = JSON.stringify(params);
            $.ajax({
                type:"POST",
                url:"<?=\library\Assets::uri('img_delete.php','api')?>",
                data:'params='+params,
                cache:false,
                success:function(data) {
                    var id = JSON.parse(data).img_data;
                    $('#'+id).attr('src',noimage);
                    alert("削除しました");
                },
                error:function(data) {
                    alert("削除に失敗しました");
                }
            });
        }
    });
    $('button#user_memo_update').click(function(){
        var params = {
            'user_id':user_id,
            'memo':$('#user_memo').val()
        };
        params = JSON.stringify(params);
        $.ajax({
            type:"POST",
            url:"<?=\library\Assets::uri('user_memo_update.php','api')?>",
            data:'params='+params,
            cache:false,
            success:function(data) {
                alert("更新しました");
            },
            error:function(data) {
                alert("更新に失敗しました");
            }
        });
    });
    $('#ban_add').click(function(){
        if(!confirm('このユーザをBANします。よろしいですか？')) {
            return;
        }
        var params = {
            'user_id':user_id,
            'reason':$('#ban_reason').val(),
            'ban_time':$('#ban_time').val(),
        }
        params = JSON.stringify(params);
        $.ajax({
            type:"POST",
            url:"<?=\library\Assets::uri('user_ban_add.php','api')?>",
            data:'params='+params,
            cache:false,
            success:function(data) {
                alert("BANしました");
                location.href = '<?=\library\Assets::uri('data/detail.php?id='.$userId)?>';
            },
            error:function(data) {
                alert("BANに失敗しました");
            }
        });
    });
    $('#ban_on').click(function(){
        if(!confirm('BAN情報を有効化します。よろしいですか？')) {
            return;
        }
        var params = {
            'user_id':user_id,
            'mode':'on'
        }
        params = JSON.stringify(params);
        $.ajax({
            type:"POST",
            url:"<?=\library\Assets::uri('user_ban_change.php','api')?>",
            data:'params='+params,
            cache:false,
            success:function(data) {
                alert("有効化しました");
                location.href = '<?=\library\Assets::uri('data/detail.php?id='.$userId)?>';
            },
            error:function(data) {
                alert("有効化に失敗しました");
            }
        });
    });
    $('#ban_off').click(function(){
        if(!confirm('BAN情報を無効化します。よろしいですか？')) {
            return;
        }
        var params = {
            'user_id':user_id,
            'mode':'off'
        }
        params = JSON.stringify(params);
        $.ajax({
            type:"POST",
            url:"<?=\library\Assets::uri('user_ban_change.php','api')?>",
            data:'params='+params,
            cache:false,
            success:function(data) {
                alert("無効化しました");
                location.href = '<?=\library\Assets::uri('data/detail.php?id='.$userId)?>';
            },
            error:function(data) {
                alert("無効化に失敗しました");
            }
        });
    });
});
</script>
<?php
require_once('../_footer.php');