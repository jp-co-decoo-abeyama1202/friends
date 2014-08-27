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
    'id','name','sex','max_age','min_age','country','area','request','publishing','profile','device','state','max_create_time','min_create_time'
);
$sortList = array(
    'id_A' => 'ID昇順',
    'id_D' => 'ID降順',
    'name_A' => '名前昇順',
    'name_D' => '名前降順',
    'age_A' => '年齢昇順',
    'age_D' => '年齢降順',
    'area' => '国・地域',
    'create_time_A' => '登録日昇順',
    'create_time_D' => '登録日降順',
    'login_time' => '最近ログインした順',
);
$offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
$count = isset($_POST['count']) ? $_POST['count'] : 30;
$values = array();
foreach($searchList as $key) {
    if(isset($_POST[$key])) {
        $bkey = ':'.$key;
        $values[$bkey] = $_POST[$key];
    }
}
$list = $storage->User->search($values);
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>ユーザ検索</h1>
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
                                    <label for="name">名前（部分一致）</label>
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
                                <div class="col-xs-2">
                                    <label for="min_age">年齢</label>
                                    <select id="min_age" name="min_age" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_ageList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':min_age'])&&$values[':min_age']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                                <div class="col-xs-1" style="width:10px;padding-left:5px;">
                                    <label for="max_age">　</label>
                                    <div class="form-control" style="border:none;padding:6px 0;">～</div>
                                </div>
                                <div class="col-xs-2">
                                    <label for="max_age">　</label>
                                    <select id="max_age" name="max_age" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_ageList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':max_age'])&&$values[':max_age']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label for="country">国</label>
                                    <select id="country" name="country" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_countryList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':country'])&&$values[':country']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                                <div class="col-xs-4">
                                    <label for="area">地域（番号指定）</label>
                                    <input type="text" id="area" name="area" class="form-control" placeholder="地域番号"  value="<?=isset($values[':area']) ? $values[':area'] : ''?>" />
                                </div>
                            </div>
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-3">
                                    <label for="request">リクエスト</label>
                                    <select id="request" name="request" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_requestList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':request'])&&$values[':request']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label for="publishing">写真公開</label>
                                    <select id="publishing" name="publishing" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_publishingList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':publishing'])&&$values[':publishing']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label for="device">デバイス</label>
                                    <select id="device" name="device" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_deviceList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':device'])&&$values[':device']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                                <div class="col-xs-3">
                                    <label for="state">状態</label>
                                    <select id="state" name="device" class="form-control">
                                        <?php foreach(\library\admin\Model_User::$_stateList as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values[':state'])&&$values[':state']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                            </div>
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-4">
                                    <label for="min_crate_time">登録時間</label>
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
                            <div class="form-group">
                                <label for="profile">自己紹介文（部分一致）</label>
                                <input type="text" id="profile" name="profile" class="form-control" placeholder="自己紹介文（一部）"  value="<?=isset($values[':profile']) ? $values[':profile'] : ''?>" />
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">検　索</button>
                                <button type="button" onclick="location.href='<?=\library\Assets::uri('data/index.php')?>'" class="btn btn-danger">クリア</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-people"> 登録者一覧</i></h3>
                    </div><!-- /.box-header -->
                    <?php if(!$list):?>
                    <div class="box-body">
                        <div class="alert alert-danger alert-dismissable">
                            <i class="fa fa-ban"></i>
                            <b>該当ユーザなし</b>
                        </div>
                    </div>
                    <?php else:?>
                    <div class="box-body table-responsive">
                        <table id="user_list" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>ID</th>
                                    <th>IDトークン</th>
                                    <th>名前</th>
                                    <th>状態</th>
                                    <th>デバイス</th>
                                    <th>国 / 地域</th>
                                    <th>登録日</th>
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
