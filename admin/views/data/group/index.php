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
    'id','name','create_user_id','delete_flag','max_create_time','min_create_time'
);
$values = array();
foreach($searchList as $key) {
    if(isset($_POST[$key])) {
        $bkey = ':'.$key;
        $values[$bkey] = $_POST[$key];
    }
}
$list = $storage->Group->search($values);
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>グループ検索</h1>
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
                                    <label for="id">ID</label>
                                    <input type="text" id="id" name="id" class="form-control" placeholder="ID or トークン" value="<?=isset($values[':id']) ? $values[':id'] : ''?>" />
                                </div>
                                <div class="col-xs-5">
                                    <label for="name">グループ名（部分一致）</label>
                                    <input type="text" id="name" name="name" class="form-control" placeholder="名前" value="<?=isset($values[':name']) ? $values[':name'] : ''?>" />
                                </div>
                                <div class="col-xs-2">
                                    <label for="age">性別</label>
                                    <select id="age" name="age" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_sexList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':sex'])&&$values[':sex']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                            </div>
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-3">
                                    <label for="state">状態</label>
                                    <select id="state" name="delete_flag" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_stateList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':state'])&&$values[':state']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                            </div>
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-4">
                                    <label for="min_crate_time">作成時間</label>
                                    <div class="input-group input-append" id="min_create_times">
                                        <div class="input-group-addon add-on"><i class="fa fa-calendar"></i></div>
                                        <input type="text" id="min_create_time" name="min_create_time" data-format="yyyy/MM/dd hh:mm:ss" class="form-control" readonly/>
                                        <div class='input-group-btn'><button type='button' class="btn btn-danger btn-del" for="min_create_time" id="del-btn"><i class="ion ion-ios7-close-empty"></i></button></div>
                                    </div><!-- /.input group -->
                                </div>
                                <div class="col-xs-1" style="width:10px;padding-left:5px;">
                                    <label>　</label>
                                    <div class="form-control" style="border:none;padding:6px 0;">～</div>
                                </div>
                                <div class="col-xs-4">
                                    <label for="max_crate_time">　</label>
                                    <div class="input-group input-append" id="max_create_times">
                                        <div class="input-group-addon add-on"><i class="fa fa-calendar"></i></div>
                                        <input type="text" id="max_create_time" name="max_create_time" data-format="yyyy/MM/dd hh:mm:ss" class="form-control" readonly/>
                                        <div class='input-group-btn'><button type='button' class="btn btn-danger btn-del" for="max_create_time" id="del-btn"><i class="ion ion-ios7-close-empty"></i></button></div>
                                    </div><!-- /.input group -->
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">検　索</button>
                                <button type="button" onclick="location.href='<?=\library\Assets::uri('data/group/index.php')?>'" class="btn btn-danger">クリア</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-people"> グループ一覧</i></h3>
                    </div><!-- /.box-header -->
                    <?php if(!$list):?>
                    <div class="box-body">
                        <div class="alert alert-danger alert-dismissable">
                            <i class="fa fa-ban"></i>
                            <b>該当グループなし</b>
                        </div>
                    </div>
                    <?php else:?>
                    <div class="box-body table-responsive">
                        <table id="user_list" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>作成者</th>
                                    <th>状態</th>
                                    <th>作成日</th>
                                    <th>最終ログイン</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($list as $row):?>
                                <tr>
                                    <td><a href="<?=\library\Assets::uri('data/detail.php?id='.$row['id'])?>"><button class="btn btn-sm btn-info">詳細</button></a></td>
                                    <td class="text-right"><?=$row['id']?></td>
                                    <td><?=$row['token']?></td>
                                    <td><?=$row['name']?></td>
                                    <td><?=icon($row['state'],'state')?></td>
                                    <td><?=icon($row['device'],'device')?></td>
                                    <td><?=\library\admin\Model_User::$_countryList[$row['country']]?> / <?=$row['area']?></td>
                                    <td class="text-right"><?=date('y/m/d H:i',$row['create_time'])?></td>
                                    <td class="text-right"><?=date('y/m/d H:i',$row['login_time'])?></td>
                                </tr>
                            <?php endforeach?>
                            </tbody>
                            <tfoot>
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
        $('#user_list').dataTable({
            "bPaginate": true,
            "bLengthChange": false,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false,
            "aaSorting":[[7,'desc']],
        });
        $('#min_create_time').datepicker({format:'yyyy/mm/dd',language:'ja'});
        $('#max_create_time').datepicker({format:'yyyy/mm/dd',language:'ja'});
        $('.btn-del').click(function(){$('#'+$(this).attr('for')).val('');});
        <?php if($list):?>
        /* 最初から非表示な分を調整 */
        $('.fa-plus')[0].click();
        <?php endif;?>
    });
</script>
<?php
require_once('../_footer.php');
