<?php
require_once('/home/homepage/html/public/friends/admin/inc/define.php');
$_cssList = array(
    'datepicker/datepicker3.css',
    'datatables/dataTables.bootstrap.css',
);
require_once(__DIR__ . '/../_header.php');
?>
<?php
$searchList = array(
    'user_id','reporter_id','message','start_time','end_time'
);
$offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
$count = isset($_POST['count']) ? $_POST['count'] : 30;
$values = array();
foreach($searchList as $key) {
    if(isset($_POST[$key])) {
        $values[$key] = $_POST[$key];
    }
}
$list = $storage->Report->search($values);
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>違反報告検索</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header bg-light-blue">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> 絞り込み</i></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-sm" data-widget="collapse"><i class="fa fa-<?=$list ? 'plus':'minus'?>"></i></button>
                        </div>
                    </div>
                    <div class="box-body" <?=$list ? 'style="display:none;"':''?>>
                        <form method="POST" role="form" id="search_form">
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-4">
                                    <label for="user_id">違反者ID</label>
                                    <input type="text" id="user_id" name="user_id" class="form-control" placeholder="ID or トークン" value="<?=isset($values['user_id']) ? $values['user_id'] : ''?>" />
                                </div>
                                <div class="col-xs-4">
                                    <label for="reporter_id">報告者ID</label>
                                    <input type="text" id="reporter_id" name="reporter_id" class="form-control" placeholder="ID or トークン" value="<?=isset($values['reporter_id']) ? $values['reporter_id'] : ''?>" />
                                </div>
                            </div>
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-4">
                                    <label for="start_time">報告日時</label>
                                    <div class="input-group input-append" id="start_times">
                                        <div class="input-group-addon add-on"><i class="fa fa-calendar"></i></div>
                                        <input type="text" id="start_time" name="start_time" data-format="yyyy/MM/dd hh:mm:ss" class="form-control" value="<?=isset($values['start_time']) ? $values['start_time'] : ''?>" readonly/>
                                        <div class='input-group-btn'><button type='button' class="btn btn-danger btn-del" for="start_time" id="del-btn"><i class="ion ion-ios7-close-empty"></i></button></div>
                                    </div><!-- /.input group -->
                                </div>
                                <div class="col-xs-1" style="width:10px;padding-left:5px;">
                                    <label>　</label>
                                    <div class="form-control" style="border:none;padding:6px 0;">～</div>
                                </div>
                                <div class="col-xs-4">
                                    <label for="start_time">　</label>
                                    <div class="input-group input-append" id="end_times">
                                        <div class="input-group-addon add-on"><i class="fa fa-calendar"></i></div>
                                        <input type="text" id="end_time" name="end_time" data-format="yyyy/MM/dd hh:mm:ss" class="form-control" value="<?=isset($values['end_time']) ? $values['end_time'] : ''?>" readonly/>
                                        <div class='input-group-btn'><button type='button' class="btn btn-danger btn-del" for="end_time" id="del-btn"><i class="ion ion-ios7-close-empty"></i></button></div>
                                    </div><!-- /.input group -->
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="message">報告文（部分一致）</label>
                                <input type="text" id="message" name="message" class="form-control" placeholder="報告文（部分一致）"  value="<?=isset($values['message']) ? $values['message'] : ''?>" />
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">検　索</button>
                                <button type="button" onclick="location.href='<?=\library\Assets::uri('data/search_report.php')?>'" class="btn btn-danger">クリア</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="box box-warning">
                    <div class="box-header bg-yellow">
                        <h3 class="box-title"><i class="ion ion-alert-circled"> 違反報告一覧</i></h3>
                    </div><!-- /.box-header -->
                    <?php if(!$list):?>
                    <div class="box-body">
                        <div class="alert alert-danger alert-dismissable">
                            <i class="fa fa-ban"></i>
                            <b>該当する違反報告がありません</b>
                        </div>
                    </div>
                    <?php else:?>
                    <div class="box-body table-responsive">
                        <table id="report_list" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width:48px;"></th>
                                    <th style="width:100px;">違反者ID</th>
                                    <th style="width:100px;">報告者ID</th>
                                    <th>報告文</th>
                                    <th style="width:100px;">報告日</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($list as $row):?>
                                <tr>
                                    <td><a href="<?=\library\Assets::uri('data/report.php?id='.$row['id'])?>"><button class="btn btn-sm btn-info">詳細</button></a></td>
                                    <td class="text-right"><a href="<?=\library\Assets::uri('data/detail.php?id='.$row['user_id'])?>"><?=$row['user_id']?></a></td>
                                    <td class="text-right"><a href="<?=\library\Assets::uri('data/detail.php?id='.$row['reporter_id'])?>"><?=$row['reporter_id']?></a></td>
                                    <td><?=escapetext($row['message'])?></td>
                                    <td class="text-right"><?=date('y/m/d H:i',$row['create_time'])?></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th>違反者ID</th>
                                    <th>報告者ID</th>
                                    <th>報告文</th>
                                    <th>報告日</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php endif?>
                </div>
            </div>
        </div>
    </section>
</aside>
<?php 
$_jsList = array(
    'plugins/datatables/jquery.dataTables.js',
    'plugins/datatables/dataTables.bootstrap.js',
    'plugins/input-mask/jquery.inputmask.js',
    'plugins/input-mask/jquery.inputmask.date.extensions.js',
    'plugins/input-mask/jquery.inputmask.extensions.js',
    'plugins/datepicker/bootstrap-datepicker.js',
    'plugins/datepicker/locales/bootstrap-datepicker.ja.js',
);
require_once('../_footContent.php');
?>
<script type="text/javascript">
    $(function() {
        $('#report_list').dataTable({
            "bPaginate": true,
            "bLengthChange": false,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false,
            "aaSorting":[[4,'desc']],
        });
        $('#start_time').datepicker({format:'yyyy/mm/dd',language:'ja'});
        $('#end_time').datepicker({format:'yyyy/mm/dd',language:'ja'});
        $('.btn-del').click(function(){$('#'+$(this).attr('for')).val('');});
        <?php if($list):?>
        /* 最初から非表示な分を調整 */
        $('.fa-plus')[0].click();
        <?php endif;?>
    });
</script>
<?php
require_once('../_footer.php');
