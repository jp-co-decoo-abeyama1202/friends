<?php
require_once(__DIR__ . '/../../inc/define.php');
$sendKey = isset($_POST['send']) ? (int)$_POST['send'] : 0;
$data = array();
$list = array(
    'udid','name','sex','age','country','area','push_id','device'
);
foreach($list as $key) {
    if(!isset($data[$key])) {
        $data[$key] = '';
    }
}
require_once(__DIR__ . '/../_header.php');
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>ユーザ登録</h1>
        <ol class="breadcrumb">
            <li><a href="<?=\library\Assets::uri('/')?>"><i class="fa fa-dashboard"></i>Dashboard</a></li>
            <li><a href="<?=\library\Assets::uri('/test/')?>">API一覧</a></li>
            <li class="active">ユーザ登録</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> 登録内容</i></h3>
                    </div>
                    <form method="POST" role="form" id="post_form">
                        <div class="box-body">
                            <?php foreach($list as $key):?>
                            <div class="form-group" id="<?=$key?>-body">
                                <label for="<?=$key?>"><?=$key?></label>
                                <input type="text" name="<?=$key?>" id="<?=$key?>" size="50" value="<?=$data[$key]?>" class="form-control"/>
                            </div>
                            <?php endforeach ?>
                        </div>
                        <div class="box-footer">
                            <button type="button" id="btn_submit" class="btn btn-primary">送信</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> 取得内容</i></h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="return_code">HTTP Response code</label>
                            <input type="text" value="" id="return_code" class="form-control" readonly/>
                        </div>
                        <div class="form-group">
                            <label for="return_body">Body</label>
                            <textarea id="return_body" rows="20" class="form-control" readonly></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</aside>
<?php
require_once(__DIR__ . '/../_footContent.php');
?>
<script>
$('#btn_submit').click(function(){
    var params = {};
    var send = true;
    $('.form-group').removeClass("has-warning");
    $('#post_form input').each(function(){
        id = $(this).attr('id');
        if(id) {
            if(!$(this).val() && (id == 'udid' || id == 'name')) {
                $('#'+id+"-body").addClass("has-warning");
                send = false;
            }
            params[id] = $(this).val();
        }
    });
    if(!send) {
        return;
    }
    params = JSON.stringify(params);
    $.ajax({
        type:"POST",
        url:"<?=\library\Assets::uri('userAdd.php','test_api')?>",
        data:'params='+params,
        cache:false,
        success:function(data,status,xhr) {
            $('#return_code').val(xhr.status);
            $('#return_body').val(data);
        },
        error:function(data,status,xhr) {
            $('#return_code').val(xhr.status);
            $('#return_body').val(data);
        }
    });
});
</script>
<?php
require_once(__DIR__ . '/../_footer.php');