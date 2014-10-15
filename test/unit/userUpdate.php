<?php
require_once('/home/homepage/html/public/friends/inc/define.php');
$sendKey = isset($_POST['send']) ? (int)$_POST['send'] : 0;
$data = array();
$list = array(
    'user_id','name','sex','age','country','area','request','publishing','profile','device','state','login_time','push_id',
    'comment_id','comment_title','comment_text',
    'option_sex','option_min_age','option_max_age','option_country','option_area','option_push_friend','option_push_chat'
);
if($sendKey === 2) {
    $url = \library\Assets::uri('userUpdate.php','test_api');
    foreach($list as $key) {
        if(isset($_POST[$key]) && $_POST[$key]) {
            $data[$key] = $_POST[$key];
        }
    }
    list($code,$body) = apiTest($url,$data);
    echo "Header:".$http_response_header[0]."<br/>";
    echo "BODY:".$body."<br/><hr/>";
}
if($sendKey > 0) {
    //編集ユーザ取得
    $userId = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    if($userId) {
        if(!is_numeric($userId)) {
            $data = $storage->User->getDataFromToken($userId);
        } else {
            $data = $storage->User->primaryOne($userId);
        }
        if($data) {
            $id = $data['id'];
            $data['user_id'] = $storage->UserToken->getToken($id);
        }
    }
}
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
                        <form method="POST">
                            <?php if($data['user_id']): ?>
                            <input type="hidden" name="user_id" size="50" value="<?=$data['user_id']?>" />
                            user_id:<?=$data['user_id']?><br/>
                            <?php else:?>
                            user_id:<input type="text" name="user_id" size="50" value="" />
                            <?php endif?>
                            <?php if($sendKey > 0): ?>
                            <table border="1">
                                <?php foreach($list as $key):if($key==='user_id'){continue;}?>
                                <tr><th><?=$key?></th><td><input type="text" name="<?=$key?>" size="50" value="<?=$data[$key]?>"/></td></tr>
                                <?php endforeach ?>
                            </table>
                            <?php endif?>
                            <input type="hidden" name="send" value="<?=$sendKey === 0 ? 1 : 2?>"/>
                            <input type="submit" value="送信" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</aside>