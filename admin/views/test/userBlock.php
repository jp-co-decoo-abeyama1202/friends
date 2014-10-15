<?php
require_once(__DIR__ . '/../../inc/define.php');
$sendKey = isset($_POST['send']) ? (int)$_POST['send'] : 0;
$data = array();
$userId = isset($_GET['user_id']) ? $_GET['user_id'] : null;
$blockId = isset($_GET['block_id']) ? $_GET['block_id'] : null;
if($userId && $blockId) {
    $tokens = $storage->UserToken->getTokens(array($userId,$blockId));
    if(count($tokens)==2) {
        //ユーザ間の関係を取得
        $relation = $storage->User->getRelation($userId,$blockId); 
    } else {
        $userId = $blockId = null;
    }
}
require_once(__DIR__ . '/../_header.php');
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>ユーザブロック</h1>
        <ol class="breadcrumb">
            <li><a href="<?=\library\Assets::uri('/')?>"><i class="fa fa-dashboard"></i>Dashboard</a></li>
            <li><a href="<?=\library\Assets::uri('/test/')?>">API一覧</a></li>
            <li class="active">ユーザブロック</li>
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
                                <label>user_id : <?=$tokens[$userId]?></label><br/>
                                <label>block_id : <?=$tokens[$blockId]?></label><br/>
                                <label>ユーザ間の関係 : <span style="font-weight:bold;"><?=\library\admin\Model_User::$_relationList[$relation]?></span></label>
                            </div>
                            <div class="box-footer">
                                <input type="hidden" id="user_id" value="<?=$tokens[$userId]?>" />
                                <input type="hidden" id="block_id" value="<?=$tokens[$blockId]?>" />
                                <button type="button" mode="add" class="btn btn-primary btn-submit">ブロックON</button>
                                <button type="button" mode="delete" class="btn btn-primary btn-submit">ブロックOFF</button>
                                <button type="button" onclick='location.href="<?=\library\Assets::uri('test/userBlock.php')?>"' class="btn btn-danger">リセット</button>
                            </div>
                        </div>
                    </form>
                    <?php else:?>
                    <form method="GET" role="form" id="post_form">
                        <div class="box-body">
                            <div class="form-group" id="user_id-body">
                                <label for="user_id">user_id</label>
                                <input type="text" name="user_id" id="user_id" size="50" value="" class="form-control" placeholder="user_id" />
                            </div>
                            <div class="form-group" id="user_id-body">
                                <label for="block_id">view_id</label>
                                <input type="text" name="block_id" id="block_id" size="50" value="" class="form-control" placeholder="user_id" />
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
$('.btn-submit').click(function(){
    var params = {};
    params['user_id'] = $('#user_id').val();
    params['block_id'] = $('#block_id').val();
    params['mode'] = $(this).attr('mode');
    params = JSON.stringify(params);
    $.ajax({
        type:"POST",
        url:"<?=\library\Assets::uri('userBlock.php','test_api')?>",
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