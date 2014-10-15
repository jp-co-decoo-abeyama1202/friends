<?php
require_once(__DIR__ . '/../../inc/define.php');
$sendKey = isset($_POST['send']) ? (int)$_POST['send'] : 0;
$data = array();
$fromId = isset($_GET['from_id']) ? $_GET['from_id'] : null;
$toId = isset($_GET['to_id']) ? $_GET['to_id'] : null;
if($fromId && $toId) {
    $tokens = $storage->UserToken->getTokens(array($fromId,$toId));
    if(count($tokens)==2) {
        //ユーザ間の関係を取得
        $relation = $storage->User->getRelation($fromId,$toId); 
    } else {
        $fromId = $toId = null;
    }
}
require_once(__DIR__ . '/../_header.php');
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>フレンド申請</h1>
        <ol class="breadcrumb">
            <li><a href="<?=\library\Assets::uri('/')?>"><i class="fa fa-dashboard"></i>Dashboard</a></li>
            <li><a href="<?=\library\Assets::uri('/test/')?>">API一覧</a></li>
            <li class="active">フレンド申請許可・拒否・取り消し</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> 登録内容</i></h3>
                    </div>
                    <?php if($fromId&&$toId):?>
                    <form method="POST" role="form" id="post_form">
                        <div class="box-body">
                            <div class="form-group">
                                <label>from_id : <?=$tokens[$fromId]?></label><br/>
                                <label>to_id : <?=$tokens[$toId]?></label><br/>
                                <label>ユーザ間の関係 : <span style="font-weight:bold;"><?=\library\admin\Model_User::$_relationList[$relation]?></span></label>
                            </div>
                            <div class="form-group">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="state" value="2"/>
                                        許可
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="state" value="3"/>
                                        拒否
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="state" value="4"/>
                                        取り消し
                                    </label>
                                </div>
                                <input type="radio"
                            </div>
                            <div class="box-footer">
                                <input type="hidden" id="from_id" value="<?=$tokens[$fromId]?>" />
                                <input type="hidden" id="to_id" value="<?=$tokens[$toId]?>" />
                                <button type="button" mode="send" class="btn btn-primary btn-submit">送信</button>
                                <button type="button" onclick='location.href="<?=\library\Assets::uri('test/friendRequest.php')?>"' class="btn btn-danger">リセット</button>
                            </div>
                        </div>
                    </form>
                    <?php else:?>
                    <form method="GET" role="form" id="post_form">
                        <div class="box-body">
                            <div class="form-group" id="user_id-body">
                                <label for="from_id">from_id</label>
                                <input type="text" name="from_id" id="from_id" size="50" value="" class="form-control" placeholder="user_id" />
                            </div>
                            <div class="form-group" id="user_id-body">
                                <label for="to_id">to_id</label>
                                <input type="text" name="to_id" id="to_id" size="50" value="" class="form-control" placeholder="user_id" />
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
    params['from_id'] = $('#from_id').val();
    params['to_id'] = $('#to_id').val();
    params['state'] = $('input[name="state"]:checked').val();
    params = JSON.stringify(params);
    $.ajax({
        type:"POST",
        url:"<?=\library\Assets::uri('friendRequestProcess.php','test_api')?>",
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