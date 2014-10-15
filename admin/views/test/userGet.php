<?php
require_once(__DIR__ . '/../../inc/define.php');
$sendKey = isset($_POST['send']) ? (int)$_POST['send'] : 0;
$data = array();
$userId = filter_input(INPUT_GET,'user_id',FILTER_VALIDATE_INT);
$viewId = filter_input(INPUT_GET,'view_id',FILTER_VALIDATE_INT);
$code = $body = null;
if($userId && $viewId) {
    $tokens = $storage->UserToken->getTokens(array($userId,$viewId));
    var_dump($tokens);
    if(count($tokens)==2 || (count($tokens)==1 && $userId === $viewId)) {
        list($code,$body) = apiTest(
            \library\Assets::uri('userGet.php','test_api'),
            array('user_id'=>$tokens[$userId],'view_id'=>$tokens[$viewId])
        );
        if($body) {
            $data = json_decode($body,true);
        }
    } else {
        $err = "指定されたユーザが見つかりません";
        $userId = $viewId = null;
    }
}

require_once(__DIR__ . '/../_header.php');
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>ユーザ情報閲覧</h1>
        <ol class="breadcrumb">
            <li><a href="<?=\library\Assets::uri('/')?>"><i class="fa fa-dashboard"></i>Dashboard</a></li>
            <li><a href="<?=\library\Assets::uri('/test/')?>">API一覧</a></li>
            <li class="active">ユーザ情報閲覧</li>
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
                                <label>view_id : <?=$tokens[$viewId]?></label>
                            </div>
                            <?php foreach($data as $key => $value):if(is_array($value)){continue;}?>
                            <div class="form-group" id="<?=$key?>-body">
                                <label for="<?=$key?>"><?=$key?></label>
                                <input type="text" name="<?=$key?>" id="<?=$key?>" size="50" value="<?=$value?>" class="form-control"/>
                            </div>
                            <?php endforeach ?>
                            <?php foreach($data['photo'] as $key => $value):?>
                            <div class="form-group" id="<?=$key?>-body">
                                <label for="<?=$key?>_no">no</label>
                                <input type="text" name="<?=$key?>_no" id="<?=$key?>_no" size="50" value="<?=$value['no']?>" class="form-control"/>
                                <label for="photo-<?=$key?>_image">image</label>
                                <input type="text" name="<?=$key?>_image" id="<?=$key?>_image" size="50" value="<?=$value['image']?>" class="form-control"/>
                            </div>
                            <?php endforeach ?>
                            <?php foreach($data['comment'] as $key => $value):?>
                            <div class="form-group" id="<?=$key?>-body">
                                <label for="<?=$key?>_title">title</label>
                                <input type="text" name="<?=$key?>_title" id="<?=$key?>_title" size="50" value="<?=$value['title']?>" class="form-control"/>
                                <label for="photo-<?=$key?>_text">text</label>
                                <input type="text" name="<?=$key?>_text" id="<?=$key?>_text" size="50" value="<?=$value['text']?>" class="form-control"/>
                            </div>
                            <?php endforeach ?>
                            <div class="box-footer">
                                <button type="button" onclick='location.href="<?=\library\Assets::uri('test/userGet.php')?>"' class="btn btn-danger">リセット</button>
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
                                <label for="view_id">view_id</label>
                                <input type="text" name="view_id" id="view_id" size="50" value="" class="form-control" placeholder="user_id" />
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
                            <input type="text" value="<?=$code?>" id="return_code" class="form-control" readonly/>
                        </div>
                        <div class="form-group">
                            <label for="return_body">Body</label>
                            <textarea id="return_body" rows="20" class="form-control" readonly><?=$body?></textarea>
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
        url:"<?=\library\Assets::uri('userUpdate.php','test_api')?>",
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