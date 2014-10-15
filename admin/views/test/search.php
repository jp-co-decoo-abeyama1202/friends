<?php
require_once(__DIR__ . '/../../inc/define.php');
$data = array();
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$offset = isset($_GET['offset']) ? $_GET['offset'] : 0;
if($userId) {
    if(is_numeric($userId)) {
        $data = $storage->User->primaryOne($userId);
        if(!$data) {
            $userId = 0;
        } else {
            $userId = $storage->UserToken->getToken($userId);
        }
    } else {
        $data = $storage->User->getDataFromToken($userId);
        if(!$data) {
            $userId = 0;
        }
    }
}
require_once(__DIR__ . '/../_header.php');
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>検索</h1>
        <ol class="breadcrumb">
            <li><a href="<?=\library\Assets::uri('/')?>"><i class="fa fa-dashboard"></i>Dashboard</a></li>
            <li><a href="<?=\library\Assets::uri('/test/')?>">API一覧</a></li>
            <li class="active">検索</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> 登録内容</i></h3>
                    </div>
                    <?php if($userId):?>
                    <form method="POST" role="form" id="post_form">
                        <div class="box-body">
                            <div class="form-group">
                                <label>user_id : <?=$userId?></label>
                            </div>
                            <div class="form-group" id="user_id-body">
                                <label for="offset">offset</label>
                                <input type="text" name="offset" id="offset" size="50" value="<?=$offset?>" class="form-control"/>
                            </div>
                        </div>
                        <div class="box-footer">
                            <input type="hidden" name="user_id" id="user_id" value="<?=$userId?>"/>
                            <button type="button" id="btn_submit" class="btn btn-primary">送信</button>
                            <button type="button" onclick='location.href="<?=\library\Assets::uri('test/search.php')?>"' class="btn btn-danger">リセット</button>
                        </div>
                    </form>
                    <?php else:?>
                    <form method="GET" role="form" id="post_form">
                        <div class="box-body">
                            <div class="form-group" id="user_id-body">
                                <label for="user_id">user_id</label>
                                <input type="text" name="user_id" id="user_id" size="50" value="<?=$userId?>" class="form-control"/>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">送信</button>
                        </div>
                    </form>
                    <?php endif?>
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
    if(!$('#user_id').val()||!$('#offset').val()) {
        return;
    }
    params['user_id'] = $('#user_id').val();
    params['offset'] = $('#offset').val();
    
    params = JSON.stringify(params);
    $.ajax({
        type:"POST",
        url:"<?=\library\Assets::uri('search.php','test_api')?>",
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