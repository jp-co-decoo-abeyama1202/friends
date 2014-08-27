<?php
require_once('/home/homepage/html/public/friends/admin/inc/define.php');
?>
<?php
$userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$toId = isset($_GET['friend_id']) ? (int)$_GET['friend_id'] : null;
if(!$userId || !$toId) {
    return redirect(\library\Assets::uri('data/index.php'));
}
$users = $storage->User->primary(array($userId,$toId));
if(count($users) !== 2) {
    //おかしいなぁ
}
$tokens = $storage->UserToken->getTokens(array($userId,$toId));
$requestFrom = $storage->UserRequestFrom->get($userId,$toId);
$messageId = 0;
$message = array();
if($requestFrom) {
    $messageId = $requestFrom['message_id'];
    $message = $storage->UserRequestMessage->getMessage($messageId,$userId);
}
//ユーザ間の関係を取得
$relation = $storage->User->getRelation($userId,$toId);

//HTMLスタート
$_cssList = array();
require_once(__DIR__ . '/../_header.php');
?>
<style>
th,td{vertical-align:middle!important;}
</style>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>リクエスト情報</h1>
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
                            <div class="box-header"><h3 class="box-title">ID:<?=$userId?> → ID:<?=$toId?> 間の関係：<span style="font-weight:bold;"><?=\library\admin\Model_User::$_relationList[$relation]?></span></h3></div>
                        </div>
                        <table id="user_list" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>種別</th>
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
                                <tr>
                                    <td><a href="<?=\library\Assets::uri('data/detail.php?id='.$users[$userId]['id'])?>"><button class="btn btn-sm btn-info">詳細</button></a></td>
                                    <td>送信者</td>
                                    <td><?=$users[$userId]['id']?></td>
                                    <td><?=$tokens[$userId]?></td>
                                    <td><?=$users[$userId]['name']?></td>
                                    <td><?=icon($users[$userId]['state'],'state')?></td>
                                    <td><?=\library\admin\Model_User::$_countryList[$users[$userId]['country']]?> / <?=$users[$userId]['area']?></td>
                                    <td><?=date('Y/m/d H:i:s',$users[$userId]['create_time'])?></td>
                                    <td><?=date('Y/m/d H:i:s',$users[$userId]['login_time'])?></td>
                                </tr>
                                <tr>
                                    <td><a href="<?=\library\Assets::uri('data/detail.php?id='.$users[$toId]['id'])?>"><button class="btn btn-sm btn-info">詳細</button></a></td>
                                    <td>受信者</td>
                                    <td><?=$users[$toId]['id']?></td>
                                    <td><?=$tokens[$toId]?></td>
                                    <td><?=$users[$toId]['name']?></td>
                                    <td><?=icon($users[$toId]['state'],'state')?></td>
                                    <td><?=\library\admin\Model_User::$_countryList[$users[$toId]['country']]?> / <?=$users[$toId]['area']?></td>
                                    <td><?=date('Y/m/d H:i:s',$users[$toId]['create_time'])?></td>
                                    <td><?=date('Y/m/d H:i:s',$users[$toId]['login_time'])?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <?php if(!$messageId):?>
                <div class="box-body">
                    <div class="alert alert-danger alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <b>リクエストを送った形跡がありません</b>
                    </div>
                </div>
                <?php else:?>
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-person-add"> リクエスト内容</i></h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>メッセージID</th>
                                <td><?=$messageId?></td>
                                <th>申請状況</th>
                                <td><?=icon($requestFrom['state'],'request_state')?></td>
                                <th>表示状況</th>
                                <td><?=icon($requestFrom['delete_flag'],'delete_flag')?></td>
                            </tr>
                            <tr>
                                <th>リクエスト本文</th>
                                <td colspan="5"><textarea class="form-control" readonly><?=escapetext($message['message'])?></textarea></td>
                            </tr>
                            <tr>
                                <th>初期申請日</th>
                                <td><?= date('Y/m/s H:i:s',$requestFrom['create_time'])?></td>
                                <th>最終更新日</th>
                                <td><?= date('Y/m/s H:i:s',$requestFrom['update_time'])?></td>
                                <td colspan="2"></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php endif?>
            </div>
        </div>
    </section>
</aside>
<?php 
$_jsList = array();
require_once('../_footContent.php');
?>
<?php
require_once('../_footer.php');