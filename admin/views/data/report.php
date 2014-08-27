<?php
require_once('/home/homepage/html/public/friends/admin/inc/define.php');
?>
<?php
$reportId = isset($_GET['id']) ? (int)$_GET['id'] : null;
if(!$reportId) {
    return redirect(\library\Assets::uri('data/index.php'));
}
$report = $storage->Report->primaryOne($reportId);
if(!$report) {
    return redirect(\library\Assets::uri('data/index.php'));
}
$userId = $report['user_id'];
$reporterId = $report['reporter_id'];

$users = $storage->User->primary(array($userId,$reporterId));
if(count($users) !== 2) {
    //おかしいなぁ
    return redirect(\library\Assets::uri('data/index.php'));
}
$tokens = $storage->UserToken->getTokens(array($userId,$reporterId));
//ユーザ間の関係を取得
$relation = $storage->User->getRelation($userId,$reporterId);

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
        <h1>違反者報告情報</h1>
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
                            <div class="box-header"><h3 class="box-title">ID:<?=$userId?> → ID:<?=$reporterId?> 間の関係：<span style="font-weight:bold;"><?=\library\admin\Model_User::$_relationList[$relation]?></span></h3></div>
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
                                    <td>違反者</td>
                                    <td><?=$users[$userId]['id']?></td>
                                    <td><?=$tokens[$userId]?></td>
                                    <td><?=$users[$userId]['name']?></td>
                                    <td><?=icon($users[$userId]['state'],'state')?></td>
                                    <td><?=\library\admin\Model_User::$_countryList[$users[$userId]['country']]?> / <?=$users[$userId]['area']?></td>
                                    <td><?=date('Y/m/d H:i:s',$users[$userId]['create_time'])?></td>
                                    <td><?=date('Y/m/d H:i:s',$users[$userId]['login_time'])?></td>
                                </tr>
                                <tr>
                                    <td><a href="<?=\library\Assets::uri('data/detail.php?id='.$users[$reporterId]['id'])?>"><button class="btn btn-sm btn-info">詳細</button></a></td>
                                    <td>報告者</td>
                                    <td><?=$users[$reporterId]['id']?></td>
                                    <td><?=$tokens[$reporterId]?></td>
                                    <td><?=$users[$reporterId]['name']?></td>
                                    <td><?=icon($users[$reporterId]['state'],'state')?></td>
                                    <td><?=\library\admin\Model_User::$_countryList[$users[$reporterId]['country']]?> / <?=$users[$reporterId]['area']?></td>
                                    <td><?=date('Y/m/d H:i:s',$users[$reporterId]['create_time'])?></td>
                                    <td><?=date('Y/m/d H:i:s',$users[$reporterId]['login_time'])?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-warning">
                    <div class="box-header bg-yellow">
                        <h3 class="box-title"><i class="ion ion-alert-circled"> 報告内容</i></h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered">
                            <tr>
                                <th>報告ID</th>
                                <td><?=$report['id']?></td>
                                <th>報告日</th>
                                <td><?= date('Y/m/s H:i:s',$report['create_time'])?></td>
                            </tr>
                            <tr>
                                <th>報告文</th>
                                <td colspan="3"><textarea class="form-control" readonly><?=escapetext($report['message'])?></textarea></td>
                            </tr>
                        </table>
                    </div>
                </div>
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