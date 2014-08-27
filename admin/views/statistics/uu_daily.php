<?php require_once('/home/homepage/html/public/friends/admin/inc/define.php');?>
<?php 
$start = isset($_POST['search_start']) && $_POST['search_start'] ? $_POST['search_start'] : date('Y/m/d',strtotime('first day of'));
$end = isset($_POST['search_end']) && $_POST['search_end'] ? $_POST['search_end'] : date('Y/m/d',strtotime('last day of'));
$uuCounts = array();
$uus = array(
    'total' => 0,
    'ios' => 0,
    'android' => 0,
    'sexall' => 0,
    'man' => 0,
    'woman' => 0,
    'per_ios' => 0,
    'per_android' => 0,
    'per_sexall' => 0,
    'per_man' => 0,
    'per_woman' => 0,
);
$startTime = strtotime($start);
$endTime = strtotime($end);
while($startTime < $endTime) {
    $count = $storage->Uu->getUuCount($startTime);
    $uuCounts[$startTime] = $count;
    $uus['total']   += $count['total'];
    $uus['ios']     += $count['device_'.\library\Model_User::DEVICE_IOS];
    $uus['android'] += $count['device_'.\library\Model_User::DEVICE_ANDROID];
    $uus['sexall']  += $count['sex_'.\library\Model_User::SEX_ALL];
    $uus['man']     += $count['sex_'.\library\Model_User::SEX_MAN];
    $uus['woman']   += $count['sex_'.\library\Model_User::SEX_WOMAN];
    //次の日へ
    $startTime += 86400;//1日分
}
//ksort($uuCounts);

//割合を求める
$uus['per_ios']     = $uus['total'] ? (int)($uus['ios'] / $uus['total'] * 100) : 0;
$uus['per_android'] = $uus['total'] ? (int)($uus['android'] / $uus['total'] * 100) : 0;
$uus['per_sexall']  = $uus['total'] ? (int)($uus['sexall'] / $uus['total'] * 100) : 0;
$uus['per_man']     = $uus['total'] ? (int)($uus['man'] / $uus['total'] * 100) : 0;
$uus['per_woman']   = $uus['total'] ? (int)($uus['woman'] / $uus['total'] * 100) : 0;

$_cssList = array(
    '/morris/morris.css',
    'datepicker/datepicker3.css',
    'datatables/dataTables.bootstrap.css',
);
require_once(__DIR__ . '/../_header.php');
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>UU(日別)</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header bg-light-blue">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> 絞り込み</i></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-sm" data-widget="collapse"><i class="fa fa-<?=$uuCounts ? 'plus':'minus'?>"></i></button>
                        </div>
                    </div>
                    <div class="box-body" <?=$uuCounts ? 'style="display:none;"':''?>>
                        <form method="POST" role="form" id="search_form">
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-4">
                                    <label for="search_start">絞り込み日時</label>
                                    <div class="input-group input-append" id="min_create_times">
                                        <div class="input-group-addon add-on"><i class="fa fa-calendar"></i></div>
                                        <input type="text" id="search_start" name="search_start" data-format="yyyy/MM/dd hh:mm:ss" class="form-control" value="<?=$start?>" readonly/>
                                        <div class='input-group-btn'><button type='button' class="btn btn-danger btn-del" for="search_start" id="del-btn"><i class="ion ion-ios7-close-empty"></i></button></div>
                                    </div><!-- /.input group -->
                                </div>
                                <div class="col-xs-1" style="width:10px;padding-left:5px;">
                                    <label>　</label>
                                    <div class="form-control" style="border:none;padding:6px 0;">～</div>
                                </div>
                                <div class="col-xs-4">
                                    <label for="search_end">　</label>
                                    <div class="input-group input-append" id="max_create_times">
                                        <div class="input-group-addon add-on"><i class="fa fa-calendar"></i></div>
                                        <input type="text" id="search_end" name="search_end" data-format="yyyy/MM/dd hh:mm:ss" class="form-control" value="<?=$end?>" readonly/>
                                        <div class='input-group-btn'><button type='button' class="btn btn-danger btn-del" for="search_end" id="del-btn"><i class="ion ion-ios7-close-empty"></i></button></div>
                                    </div><!-- /.input group -->
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">検　索</button>
                                <button type="button" onclick="location.href='<?=\library\Assets::uri('statistics/uu_daily.php')?>'" class="btn btn-danger">クリア</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <section class="col-lg-12"> 
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul class="nav nav-tabs pull-right" style="height:44px;">
                        <li><a href="#tab-user-sex" data-toggle="tab">性別</a></li>
                        <li><a href="#tab-user-device" data-toggle="tab">デバイス</a></li>
                        <li class="active"><a href="#tab-user" data-toggle="tab">総合</a></li>
                        <li class="pull-left header"><i class="fa fa-users"></i> <?=$start?>～<?=$end?>のUU</li>
                        <div class="btn-group" style="margin-top:5px;">
                            <button type="button" id="view-tile" class="btn btn-default">tile</button>
                            <button type="button" id="view-tab" class="btn btn-default active hover">tab</button>
                        </div>
                    </ul>
                    <div class="tab-content">
                        <div class="box chart tab-pane active" id="tab-user" style="position: relative; height: 300px;">
                            <div class="box-header">
                                <i class="fa fa-th"></i>
                                <h3 class="box-title">総合</h3>
                            </div>
                            <div class="box-body border-radius-none">
                                <div class="chart" id="user-chart" style="height: 250px;"></div>                                    
                            </div><!-- /.box-body -->
                        </div>
                        <div class="box chart tab-pane" id="tab-user-device" style="position: relative; height: 400px;">
                            <div class="box-header">
                                <i class="fa fa-th"></i>
                                <h3 class="box-title">デバイス</h3>
                            </div>
                            <div class="box-body border-radius-none">
                                <div class="chart" id="user-device-chart" style="height: 250px;"></div>                                    
                            </div><!-- /.box-body -->
                            <div class="box-footer" style="border-bottom: 1px solid #f4f4f4">
                                <div class="row">
                                    <div class="col-xs-6 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_ios']?>" data-width="60" data-height="60" data-fgColor="#ABABAB"/>
                                        <div class="knob-label">iOS</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-6 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_android']?>" data-width="60" data-height="60" data-fgColor="#A4F84A"/>
                                        <div class="knob-label">Android</div>
                                    </div><!-- ./col -->
                                </div><!-- /.row -->
                            </div><!-- /.box-footer -->
                        </div>
                        <div class="box chart tab-pane" id="tab-user-sex" style="position: relative; height: 400px;">
                            <div class="box-header">
                                <i class="fa fa-th"></i>
                                <h3 class="box-title">性別</h3>
                            </div>
                            <div class="box-body border-radius-none">
                                <div class="chart" id="user-sex-chart" style="height: 250px;"></div>                                    
                            </div><!-- /.box-body -->
                            <div class="box-footer" style="border-bottom: 1px solid #f4f4f4">
                                <div class="row">
                                    <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_man']?>" data-width="60" data-height="60" data-fgColor="#00c0ef"/>
                                        <div class="knob-label">男性</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_woman']?>" data-width="60" data-height="60" data-fgColor="#f56954"/>
                                        <div class="knob-label">女性</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-4 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_sexall']?>" data-width="60" data-height="60" data-fgColor="#00a65a"/>
                                        <div class="knob-label">その他</div>
                                    </div><!-- ./col -->
                                </div><!-- /.row -->
                            </div><!-- /.box-footer -->
                        </div>
                    </div>
                    <?php if($uuCounts):?>
                    <div class="box" style="border-top:none;">
                        <div class="box-body table-responsive">
                            <table id="uu_list" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th rowspan="2" style="width:100px;">日付</th>
                                        <th rowspan="2">計</th>
                                        <th colspan="4">デバイス</th>
                                        <th colspan="6">性別</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" style="width:150px;">iOS</th>
                                        <th colspan="2" style="width:150px;">Android</th>
                                        <th colspan="2" style="width:100px;">男性</th>
                                        <th colspan="2" style="width:100px;">女性</th>
                                        <th colspan="2" style="width:100px;">その他</th>
                                    </tr>
                                </thead>
                                <tbody class="text-right">
                                    <?php foreach($uuCounts as $time => $uuCount):
                                        $total = $uuCount['total'];
                                        $ios = $uuCount['device_'.\library\Model_User::DEVICE_IOS];
                                        $android = $uuCount['device_'.\library\Model_User::DEVICE_ANDROID];
                                        $man = $uuCount['sex_'.\library\Model_User::SEX_MAN];
                                        $woman = $uuCount['sex_'.\library\Model_User::SEX_WOMAN];
                                        $sexall = $uuCount['sex_'.\library\Model_User::SEX_ALL];
                                    ?>
                                    <tr>
                                        <td style="font-weight:bold;"><?=date('Y/m/d',$time)?>(<?=getWeek($time)?>)</td>
                                        <td><?=number_format($total)?></td>
                                        <td><?=number_format($ios)?></td>
                                        <td style="width:50px;"><?=$total ? number_format(($ios / $total * 100),2).'%' : '-'?></td>
                                        <td><?=number_format($android)?></td>
                                        <td style="width:50px;"><?=$total ? number_format(($android / $total * 100),2).'%' : '-'?></td>
                                        <td><?=number_format($man)?></td>
                                        <td style="width:50px;"><?=$total ? number_format(($man / $total * 100),2).'%' : '-'?></td>
                                        <td><?=number_format($woman)?></td>
                                        <td style="width:50px;"><?=$total ? number_format(($woman / $total * 100),2).'%' : '-'?></td>
                                        <td><?=number_format($sexall)?></td>
                                        <td style="width:50px;"><?=$total ? number_format(($sexall / $total * 100),2).'%' : '-'?></td>
                                    </tr>
                                    <?php endforeach?>
                                    <tr style="font-weight:bold;">
                                        <td>総計</td>
                                        <td><?=number_format($uus['total'])?></td>
                                        <td><?=number_format($uus['ios'])?></td>
                                        <td><?=$uus['total'] ? number_format($uus['ios']/$uus['total']*100,2).'%':'-'?></td>
                                        <td><?=number_format($uus['android'])?></td>
                                        <td><?=$uus['total'] ? number_format($uus['android']/$uus['total']*100,2).'%':'-'?></td>
                                        <td><?=number_format($uus['man'])?></td>
                                        <td><?=$uus['total'] ? number_format($uus['man']/$uus['total']*100,2).'%':'-'?></td>
                                        <td><?=number_format($uus['woman'])?></td>
                                        <td><?=$uus['total'] ? number_format($uus['woman']/$uus['total']*100,2).'%':'-'?></td>
                                        <td><?=number_format($uus['sexall'])?></td>
                                        <td><?=$uus['total'] ? number_format($uus['sexall']/$uus['total']*100,2).'%':'-'?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>日付</th>
                                        <th>計</th>
                                        <th colspan="2">iOS</th>
                                        <th colspan="2">Android</th>
                                        <th colspan="2">男性</th>
                                        <th colspan="2">女性</th>
                                        <th colspan="2">その他</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <?php endif?>
                </div>
                 
            </section>
           
        </div>
    </section>
</aside>

<?php 
$_jsList = array(
    'plugins/datatables/jquery.dataTables.js',
    'plugins/datatables/dataTables.bootstrap.js',
    'plugins/jqueryKnob/jquery.knob.js',
    'plugins/datepicker/bootstrap-datepicker.js',
    'plugins/datepicker/locales/bootstrap-datepicker.ja.js',
);
require_once(__DIR__ . '/../_footContent.php');
?>
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="<?=\library\Assets::uri('/plugins/morris/morris.min.js','js')?>" type="text/javascript"></script>
<script>
$(function() {
    "use strict";
var uu_data = [];
var uu_device_data = [];
var uu_sex_data = [];
<?php foreach($uuCounts as $time => $uuCount):?>
uu_data.push({y: '<?=date('Y-m-d',$time)?>', register: <?=$uuCount['total']?>});
uu_device_data.push({y: '<?=date('Y-m-d',$time)?>', ios: <?=$uuCount['device_'.\library\Model_User::DEVICE_IOS]?>, android: <?=$uuCount['device_'.\library\Model_User::DEVICE_ANDROID]?>});
uu_sex_data.push({y: '<?=date('Y-m-d',$time)?>', all: <?=$uuCount['sex_'.\library\Model_User::SEX_ALL]?>, man: <?=$uuCount['sex_'.\library\Model_User::SEX_MAN]?>, woman: <?=$uuCount['sex_'.\library\Model_User::SEX_WOMAN]?>});
<?php endforeach?>
    /* jQueryKnob */
    $(".knob").knob();
    /* Morris.js Charts */
    // Sales chart
    var user = new Morris.Line({
        element: 'user-chart',
        resize: true,
        data: uu_data,
        xkey: 'y',
        ykeys: ['register'],
        labels: ['登録者'],
        lineColors: ['#3c8dbc'],
        hideHover: 'auto'
    });
    var uu_device = new Morris.Line({
        element: 'user-device-chart',
        resize: true,
        data: uu_device_data,
        xkey: 'y',
        ykeys: ['ios', 'android'],
        labels: ['iOS', 'Android'],
        lineColors: ['#ABABAB', '#A4F84A'],
        hideHover: 'auto'
    });
    var uu_sex = new Morris.Line({
        element: 'user-sex-chart',
        resize: true,
        data: uu_sex_data,
        xkey: 'y',
        ykeys: ['all', 'man', 'woman'],
        labels: ['未指定', '男性', '女性'],
        lineColors: ['#00a65a', '#00c0ef', '#f56954'],
        hideHover: 'auto'
    });
    //Fix for charts under tabs
    $('ul.nav > li >a').on('shown.bs.tab', function(e) {
        user.redraw();
        uu_device.redraw();
        uu_sex.redraw();
    });
    $('#view-tile').click(function(){
        $('ul.nav-tabs > li:not(.header)').hide();
        $('div.tab-content > div').addClass('active');
        user.redraw();
        uu_device.redraw();
        uu_sex.redraw();
        $(this).addClass('active hover');
        $('#view-tab').removeClass('active hover');
    });
    $('#view-tab').click(function(){
        $('ul.nav-tabs > li:not(.header)').show();
        $('div.tab-content > div:not('+$('ul.nav-tabs > li.active > a').attr('href')+')').removeClass('active');
        user.redraw();
        uu_device.redraw();
        uu_sex.redraw();
        $(this).addClass('active hover');
        $('#view-tile').removeClass('active hover');
    });
    $('#search_start').datepicker({format:'yyyy/mm/dd',language:'ja'});
    $('#search_end').datepicker({format:'yyyy/mm/dd',language:'ja'});
    $('.btn-del').click(function(){$('#'+$(this).attr('for')).val('');});
    <?php if($uuCounts):?>
    /* 最初から非表示な分を調整 */
    $('.fa-plus')[0].click();
    <?php endif;?>
});
</script>
<?php require_once(__DIR__ . '/../_footer.php');?>