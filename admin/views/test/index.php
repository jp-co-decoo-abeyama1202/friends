<?php
require_once(__DIR__ . '/../../inc/define.php');
$list = array(
    'appVersion' => 'アプリバージョンチェック',
    'appVersionCompare' => 'アプリバージョン比較',
    'userAdd' => 'ユーザ登録',
    'userUpdate' => 'ユーザ情報更新',
    'userGet' => 'ユーザ情報取得',
    'userImageAdd' => 'プロフィール画像登録',
    'userImageDelete' => 'プロフィール画像削除',
    'userPhotoAdd' => '写真画像登録',
    'userPhotoDelete' => '写真画像削除',
    'search' => '検索',
    'userBlock' => '他ユーザをブロックする',
    'friendRequest' => 'フレンド申請する',
    'friendRequestProcess' => 'フレンド申請の許可・拒否・取り消しを行う',
    'friendRequestList' => '申請中一覧を取得する',
    'friendDelete' => 'フレンドを解除する',
    'messagePost' => 'メッセージを送信する',
    'messageGet' => 'メッセージを取得する',
    'messageRead' => 'メッセージを既読にする',
    'report' => '違反ユーザを通報する',
);
$i = 0;
require_once(__DIR__ . '/../_header.php');
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>API一覧</h1>
        <ol class="breadcrumb">
            <li><a href="<?=\library\Assets::uri('/')?>"><i class="fa fa-dashboard"></i>Dashboard</a></li>
            <li class="active">API一覧</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-people"> API一覧</i></h3>
                    </div><!-- /.box-header -->
                    <div class="box-body table-responsive">
                        <table id="user_list" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-right">No</th>
                                    <th>API名</th>
                                    <th>内容</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($list as $key => $text):++$i;?>
                                <tr>
                                    <td class="text-right"><?=$i?></td>
                                    <td><?=$key?></td>
                                    <td><?=$text?></td>
                                    <td class="text-center"><a href="<?=\library\Assets::uri('test/'.$key.".php")?>"><button class="btn btn-sm btn-danger">確認する</button></a></td>
                                    
                                </tr>
                            <?php endforeach?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-right">No</th>
                                    <th>API名</th>
                                    <th>内容</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</aside>
<?php
require_once(__DIR__ . '/../_footContent.php');
require_once(__DIR__ . '/../_footer.php');