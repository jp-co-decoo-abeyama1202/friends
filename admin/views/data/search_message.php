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
    'search_id','search_text','search_type','search_start','search_end','search_read'
);
$values = array();
foreach($searchList as $key) {
    if(isset($_POST[$key])) {
        $values[$key] = $_POST[$key];
    }
}
$list = $storage->Message->search($values);
$messageTableData = array();
foreach($list as $key => $message) {
    $w = array();
    $messageUrl = \library\Assets::uri('data/message.php?id='.$message['sender_id'].'&friends_id='.$message['friends_id']);
    $w[] = $key;
    $w[] = array(
        'params' => 'class="text-right"',
        'escape' => false,
        'value' => '<a href="'.\library\Assets::uri('data/detail.php?id='.$message['sender_id']).'">'.$message['sender_id'].'</a>',
    );
    $w[] = $message['message'];
    $w[] = array(
        'params' => 'class="text-center"',
        'escape' => false,
        'value' => icon($message['read_flag'],'read_flag'),
    );
    $w[] = date('y/m/d H:i:s',$message['create_time']);
    $w[] = $message['create_time'] == $message['update_time'] ? '-' :date('y/m/d H:i:s',$message['update_time']);
    $w[] = array(
        'params' => 'class="text-center"',
        'escape' => false,
        'value' => '<a href="' . $messageUrl . '"><button class="btn btn-sm btn-success">閲　覧</button></a>',
    );
    $messageTableData[] = $w;
}
$messageTableContents = array(
    'box_class' => 'box-info',
    'table_id' => 'search_list',
    'table_icon' => 'ion ion-ios7-chatboxes-outline',
    'table_title' => '検索結果',
    'table_header' => array('ID','送信者','メッセージ','既読','送信日時','既読日時','会話ログ'),
    'table_data' => $messageTableData,
    'error_message' => '該当する会話ログがありません',
);
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>会話検索</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> 会話検索</i></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-sm" data-widget="collapse"><i class="fa fa-<?=$list ? 'plus':'minus'?>"></i></button>
                        </div>
                    </div>
                    <div class="box-body" <?=$list ? 'style="display:none;"':''?>>
                        <form method="POST" role="form" id="search_form">
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-4">
                                    <label for="id">送信者ID</label>
                                    <input type="text" id="search_id" name="search_id" class="form-control" placeholder="ID or トークン" value="<?=isset($values['search_id']) ? $values['search_id'] : ''?>" />
                                </div>
                                <div class="col-xs-5">
                                    <label for="search_text">テキスト</label>
                                    <input type="text" id="search_text" name="search_text" class="form-control" placeholder="名前" value="<?=isset($values['search_text']) ? $values['search_text'] : ''?>" />
                                </div>
                                <div class="col-xs-2">
                                    <label for="search_type">検索タイプ</label>
                                    <select id="search_type" name="search_type" class="form-control">
                                        <?php foreach(\library\Model_Message::$_searchMessageTypes as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values['search_type'])&&$values['search_type']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                            </div>
                            <div class="row" style="padding-bottom:8px;">
                                <div class="col-xs-4">
                                    <label for="search_start">送信時間</label>
                                    <div class="input-group input-append" id="min_create_times">
                                        <div class="input-group-addon add-on"><i class="fa fa-calendar"></i></div>
                                        <input type="text" id="search_start" name="search_start" data-format="yyyy/MM/dd hh:mm:ss" class="form-control" readonly/>
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
                                        <input type="text" id="search_end" name="search_end" data-format="yyyy/MM/dd hh:mm:ss" class="form-control" readonly/>
                                        <div class='input-group-btn'><button type='button' class="btn btn-danger btn-del" for="search_end" id="del-btn"><i class="ion ion-ios7-close-empty"></i></button></div>
                                    </div><!-- /.input group -->
                                </div>
                                <div class="col-xs-2">
                                    <label for="search_read">既読</label>
                                    <select id="search_read" name="search_read" class="form-control">
                                        <?php foreach(\library\Model_Message::$_searchReadFlag as $key => $value):?>
                                        <option value="<?=$key?>" <?=isset($values['search_read'])&&$values['search_read']==$key?'selected':''?>><?=$value?></option>
                                        <?php endforeach?>
                                    </select>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">検　索</button>
                                <button type="button" onclick="location.href='<?=\library\Assets::uri('data/search_message.php')?>'" class="btn btn-danger">クリア</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <?=createBoxTable($messageTableContents);?>
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
        $('#search_list').dataTable({
            "bPaginate": true,
            "bLengthChange": false,
            "bFilter": true,
            "bSort": true,
            "bInfo": true,
            "bAutoWidth": false,
            "aaSorting":[[4,'desc']],
        });
        $('#search_start').datepicker({format:'yyyy/mm/dd',language:'ja'});
        $('#search_end').datepicker({format:'yyyy/mm/dd',language:'ja'});
        $('.btn-del').click(function(){$('#'+$(this).attr('for')).val('');});
        <?php if($list):?>
        /* 最初から非表示な分を調整 */
        $('.fa-plus')[0].click();
        <?php endif;?>
    });
</script>
<?php
require_once('../_footer.php');
