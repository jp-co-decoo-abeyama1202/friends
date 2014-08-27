<?php require_once('/home/homepage/html/public/friends/admin/inc/define.php');?>
<?php 
//必要なもの集計
$registCount = $storage->User->getRegisterUserCount();
$uuCount = $storage->Uu->getUuCount();
//過去分集計
$registCounts = array();
$uuCounts = array();
$regists = array(
    'total' => $registCount['total'],
    'ios' => $registCount['device_'.\library\Model_User::DEVICE_IOS],
    'android' => $registCount['device_'.\library\Model_User::DEVICE_ANDROID],
    'sexall' => $registCount['sex_'.\library\Model_User::SEX_ALL],
    'man' => $registCount['sex_'.\library\Model_User::SEX_MAN],
    'woman' => $registCount['sex_'.\library\Model_User::SEX_WOMAN],
);
$uus = array(
    'total' => $uuCount['total'],
    'ios' => $uuCount['device_'.\library\Model_User::DEVICE_IOS],
    'android' => $uuCount['device_'.\library\Model_User::DEVICE_ANDROID],
    'sexall' => $uuCount['sex_'.\library\Model_User::SEX_ALL],
    'man' => $uuCount['sex_'.\library\Model_User::SEX_MAN],
    'woman' => $uuCount['sex_'.\library\Model_User::SEX_WOMAN],
);
for($i=1;$i<10;$i++) {
    $time = strtotime(date('Y/m/d 00:00:00',strtotime('-'.$i.' day')));
    $count = $storage->User->getRegisterUserCount($time);
    $registCounts[$time] = $count;
    $regists['total']   += $count['total'];
    $regists['ios']     += $count['device_'.\library\Model_User::DEVICE_IOS];
    $regists['android'] += $count['device_'.\library\Model_User::DEVICE_ANDROID];
    $regists['sexall']  += $count['sex_'.\library\Model_User::SEX_ALL];
    $regists['man']     += $count['sex_'.\library\Model_User::SEX_MAN];
    $regists['woman']   += $count['sex_'.\library\Model_User::SEX_WOMAN];
    $count = $storage->Uu->getUuCount($time);
    $uuCounts[$time] = $count;
    $uus['total']   += $count['total'];
    $uus['ios']     += $count['device_'.\library\Model_User::DEVICE_IOS];
    $uus['android'] += $count['device_'.\library\Model_User::DEVICE_ANDROID];
    $uus['sexall']  += $count['sex_'.\library\Model_User::SEX_ALL];
    $uus['man']     += $count['sex_'.\library\Model_User::SEX_MAN];
    $uus['woman']   += $count['sex_'.\library\Model_User::SEX_WOMAN];
}
$registCounts[strtotime(date('Y/m/d 00:00:00'))] = $registCount;
$uuCounts[strtotime(date('Y/m/d 00:00:00'))] = $uuCount;
ksort($registCounts);

//割合を求める
//登録者
$regists['per_ios']     = $regists['total'] ? (int)($regists['ios'] / $regists['total'] * 100) : 0;
$regists['per_android'] = $regists['total'] ? (int)($regists['android'] / $regists['total'] * 100) : 0;
$regists['per_sexall']  = $regists['total'] ? (int)($regists['sexall'] / $regists['total'] * 100) : 0;
$regists['per_man']     = $regists['total'] ? (int)($regists['man'] / $regists['total'] * 100) : 0;
$regists['per_woman']   = $regists['total'] ? (int)($regists['woman'] / $regists['total'] * 100) : 0;
//UU
$uus['per_ios']     = $uus['total'] ? (int)($uus['ios'] / $uus['total'] * 100) : 0;
$uus['per_android'] = $uus['total'] ? (int)($uus['android'] / $uus['total'] * 100) : 0;
$uus['per_sexall']  = $uus['total'] ? (int)($uus['sexall'] / $uus['total'] * 100) : 0;
$uus['per_man']     = $uus['total'] ? (int)($uus['man'] / $uus['total'] * 100) : 0;
$uus['per_woman']   = $uus['total'] ? (int)($uus['woman'] / $uus['total'] * 100) : 0;
$_cssList = array(
    '/morris/morris.css',
);
require_once(__DIR__ . '/_header.php');
?>
<style>
sup{top:0.5em;}
</style>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>Dashboard<small>トップページ</small></h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-lg-6 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3><?=number_format($registCount['total'])?><sup style="font-size: 20px">人</sup></h3>
                        <p>本日の登録ユーザ数</p>
                    </div>
                    <div class="icon"><i class="ion ion-person-add"></i></div>
                    <a href="<?=\library\Assets::uri('data/index.php')?>" class="small-box-footer">
                        ユーザ情報 <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div><!-- ./col -->
            <div class="col-lg-6 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?=number_format($uuCount['total'])?><sup style="font-size: 20px">人</sup></h3>
                        <p>本日のUU</p>
                    </div>
                    <div class="icon"><i class="ion ion-pie-graph"></i></div>
                    <a href="#" class="small-box-footer">
                        集計情報 <i class="fa fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div><!-- ./col -->
        </div><!-- /.row -->
        <!-- 過去10日間の登録ユーザ -->
        <div class="row">
            <section class="col-lg-12 connectedSortable"> 
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul id="user-tabs" class="nav nav-tabs pull-right">
                        <li><a href="#tab-user-sex" data-toggle="tab">性別</a></li>
                        <li><a href="#tab-user-device" data-toggle="tab">デバイス</a></li>
                        <li class="active"><a href="#tab-user" data-toggle="tab">総合</a></li>
                        <li class="pull-left header"><i class="fa fa-users"></i> 過去10日間の登録ユーザ</li>
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
                                <h3 class="box-title">デバイス別</h3>
                            </div>
                            <div class="box-body border-radius-none">
                                <div class="chart" id="user-device-chart" style="height: 250px;"></div>                                    
                            </div><!-- /.box-body -->
                            <div class="box-footer" style="border-bottom: 1px solid #f4f4f4">
                                <div class="row">
                                    <div class="col-xs-6" style="border-right: 1px solid #f4f4f4">
                                        <table class="table table-bordered">
                                            <tr><th>iOS</th><th>Android</th><th>計</th></tr>
                                            <tr style="text-align:right;"><td><?=number_format($regists['ios'])?></td><td><?=number_format($regists['android'])?></td><td><?=number_format($regists['total'])?></tr>
                                        </table>
                                    </div>
                                    <div class="col-xs-3 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$regists['per_ios']?>" data-width="60" data-height="60" data-fgColor="#ABABAB"/>
                                        <div class="knob-label">iOS</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-3 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$regists['per_android']?>" data-width="60" data-height="60" data-fgColor="#A4F84A"/>
                                        <div class="knob-label">Android</div>
                                    </div><!-- ./col -->
                                </div><!-- /.row -->
                            </div><!-- /.box-footer -->
                        </div>
                        <div class="box chart tab-pane" id="tab-user-sex" style="position: relative; height: 400px;">
                            <div class="box-header">
                                <i class="fa fa-th"></i>
                                <h3 class="box-title">性別別</h3>
                            </div>
                            <div class="box-body border-radius-none">
                                <div class="chart" id="user-sex-chart" style="height: 250px;"></div>                                    
                            </div><!-- /.box-body -->
                            <div class="box-footer" style="border-bottom: 1px solid #f4f4f4">
                                <div class="row">
                                    <div class="col-xs-6" style="border-right: 1px solid #f4f4f4">
                                        <table class="table table-bordered">
                                            <tr><th>男性</th><th>女性</th><th>その他</th><th>計</th></tr>
                                            <tr style="text-align:right;"><td><?=number_format($regists['man'])?></td><td><?=number_format($regists['woman'])?></td><td><?=number_format($regists['sexall'])?></td><td><?=number_format($regists['total'])?></tr>
                                        </table>
                                    </div>
                                    <div class="col-xs-2 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$regists['per_man']?>" data-width="60" data-height="60" data-fgColor="#00c0ef"/>
                                        <div class="knob-label">男性</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-2 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$regists['per_woman']?>" data-width="60" data-height="60" data-fgColor="#f56954"/>
                                        <div class="knob-label">女性</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-2 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$regists['per_sexall']?>" data-width="60" data-height="60" data-fgColor="#00a65a"/>
                                        <div class="knob-label">その他</div>
                                    </div><!-- ./col -->
                                </div><!-- /.row -->
                            </div><!-- /.box-footer -->
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <!-- 過去10日間のUU -->
        <div class="row">
            <section class="col-lg-12 connectedSortable"> 
                <div class="nav-tabs-custom">
                    <!-- Tabs within a box -->
                    <ul id="uu-tabs" class="nav nav-tabs pull-right">
                        <li><a href="#tab-uu-sex" data-toggle="tab">性別</a></li>
                        <li><a href="#tab-uu-device" data-toggle="tab">デバイス</a></li>
                        <li class="active"><a href="#tab-uu" data-toggle="tab">総合</a></li>
                        <li class="pull-left header"><i class="ion ion-pie-graph"></i> 過去10日間のUU</li>
                    </ul>
                    <div class="tab-content">
                        <div class="box chart tab-pane active" id="tab-uu" style="position: relative; height: 300px;">
                            <div class="box-header">
                                <i class="fa fa-th"></i>
                                <h3 class="box-title">総合</h3>
                            </div>
                            <div class="box-body border-radius-none">
                                <div class="chart" id="uu-chart" style="height: 250px;"></div>                                    
                            </div><!-- /.box-body -->
                        </div>
                        <div class="box chart tab-pane" id="tab-uu-device" style="position: relative; height: 400px;">
                            <div class="box-header">
                                <i class="fa fa-th"></i>
                                <h3 class="box-title">デバイス別</h3>
                            </div>
                            <div class="box-body border-radius-none">
                                <div class="chart" id="uu-device-chart" style="height: 250px;"></div>                                    
                            </div><!-- /.box-body -->
                            <div class="box-footer" style="border-bottom: 1px solid #f4f4f4">
                                <div class="row">
                                    <div class="col-xs-6" style="border-right: 1px solid #f4f4f4">
                                        <table class="table table-bordered">
                                            <tr><th>iOS</th><th>Android</th><th>計</th></tr>
                                            <tr style="text-align:right;"><td><?=number_format($uus['ios'])?></td><td><?=number_format($uus['android'])?></td><td><?=number_format($uus['total'])?></tr>
                                        </table>
                                    </div>
                                    <div class="col-xs-3 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_ios']?>" data-width="60" data-height="60" data-fgColor="#ABABAB"/>
                                        <div class="knob-label">iOS</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-3 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_android']?>" data-width="60" data-height="60" data-fgColor="#A4F84A"/>
                                        <div class="knob-label">Android</div>
                                    </div><!-- ./col -->
                                </div><!-- /.row -->
                            </div><!-- /.box-footer -->
                        </div>
                        <div class="box chart tab-pane" id="tab-uu-sex" style="position: relative; height: 400px;">
                            <div class="box-header">
                                <i class="fa fa-th"></i>
                                <h3 class="box-title">性別別</h3>
                            </div>
                            <div class="box-body border-radius-none">
                                <div class="chart" id="uu-sex-chart" style="height: 250px;"></div>                                    
                            </div><!-- /.box-body -->
                            <div class="box-footer" style="border-bottom: 1px solid #f4f4f4">
                                <div class="row">
                                    <div class="col-xs-6" style="border-right: 1px solid #f4f4f4">
                                        <table class="table table-bordered">
                                            <tr><th>男性</th><th>女性</th><th>その他</th><th>計</th></tr>
                                            <tr style="text-align:right;"><td><?=number_format($uus['man'])?></td><td><?=number_format($uus['woman'])?></td><td><?=number_format($uus['sexall'])?></td><td><?=number_format($regists['total'])?></tr>
                                        </table>
                                    </div>
                                    <div class="col-xs-2 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_man']?>" data-width="60" data-height="60" data-fgColor="#00c0ef"/>
                                        <div class="knob-label">男性</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-2 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_woman']?>" data-width="60" data-height="60" data-fgColor="#f56954"/>
                                        <div class="knob-label">女性</div>
                                    </div><!-- ./col -->
                                    <div class="col-xs-2 text-center" style="border-right: 1px solid #f4f4f4">
                                        <input type="text" class="knob" data-readonly="true" value="<?=$uus['per_sexall']?>" data-width="60" data-height="60" data-fgColor="#00a65a"/>
                                        <div class="knob-label">その他</div>
                                    </div><!-- ./col -->
                                </div><!-- /.row -->
                            </div><!-- /.box-footer -->
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>
</aside>

<?php 
$_jsList = array(
    'plugins/jqueryKnob/jquery.knob.js',
);
require_once(__DIR__ . '/_footContent.php');
?>
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="<?=\library\Assets::uri('/plugins/morris/morris.min.js','js')?>" type="text/javascript"></script>
<script>
$(function() {
    "use strict";
var user_data = [];
var user_device_data = [];
var user_sex_data = [];
var uu_data = [];
var uu_device_data = [];
var uu_sex_data = [];
<?php foreach($registCounts as $time => $registCount):?>
user_data.push({y: '<?=date('Y-m-d',$time)?>', register: <?=$registCount['total']?>});
user_device_data.push({y: '<?=date('Y-m-d',$time)?>', ios: <?=$registCount['device_'.\library\Model_User::DEVICE_IOS]?>, android: <?=$registCount['device_'.\library\Model_User::DEVICE_ANDROID]?>});
user_sex_data.push({y: '<?=date('Y-m-d',$time)?>', all: <?=$registCount['sex_'.\library\Model_User::SEX_ALL]?>, man: <?=$registCount['sex_'.\library\Model_User::SEX_MAN]?>, woman: <?=$registCount['sex_'.\library\Model_User::SEX_WOMAN]?>});
<?php endforeach?>
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
        data: user_data,
        xkey: 'y',
        ykeys: ['register'],
        labels: ['登録者'],
        lineColors: ['#3c8dbc'],
        hideHover: 'auto'
    });
    var user_device = new Morris.Line({
        element: 'user-device-chart',
        resize: true,
        data: user_device_data,
        xkey: 'y',
        ykeys: ['ios', 'android'],
        labels: ['iOS', 'Android'],
        lineColors: ['#ABABAB', '#A4F84A'],
        hideHover: 'auto'
    });
    var user_sex = new Morris.Line({
        element: 'user-sex-chart',
        resize: true,
        data: user_sex_data,
        xkey: 'y',
        ykeys: ['all', 'man', 'woman'],
        labels: ['未指定', '男性', '女性'],
        lineColors: ['#00a65a', '#00c0ef', '#f56954'],
        hideHover: 'auto'
    });
    var uu = new Morris.Line({
        element: 'uu-chart',
        resize: true,
        data: uu_data,
        xkey: 'y',
        ykeys: ['register'],
        labels: ['UU'],
        lineColors: ['#3c8dbc'],
        hideHover: 'auto'
    });
    var uu_device = new Morris.Line({
        element: 'uu-device-chart',
        resize: true,
        data: uu_device_data,
        xkey: 'y',
        ykeys: ['ios', 'android'],
        labels: ['iOS', 'Android'],
        lineColors: ['#ABABAB', '#A4F84A'],
        hideHover: 'auto'
    });
    var uu_sex = new Morris.Line({
        element: 'uu-sex-chart',
        resize: true,
        data: uu_sex_data,
        xkey: 'y',
        ykeys: ['all', 'man', 'woman'],
        labels: ['未指定', '男性', '女性'],
        lineColors: ['#00a65a', '#00c0ef', '#f56954'],
        hideHover: 'auto'
    });
    //Fix for charts under tabs
    $('ul#user-tabs > li >a').on('shown.bs.tab', function(e) {
        user.redraw();
        user_device.redraw();
        user_sex.redraw();
    });
    $('ul#uu-tabs > li >a').on('shown.bs.tab', function(e) {
        uu.redraw();
        uu_device.redraw();
        uu_sex.redraw();
    });
});
</script>
<?php require_once(__DIR__ . '/_footer.php');?>