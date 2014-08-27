<?php
require_once(__DIR__ . '/../_header.php');
$sendKey = isset($_POST['send']) ? (int)$_POST['send'] : 0;
$data = array();
$list = array(
    'user_id','name','sex','age','country','area','request','publishing','profile','device','state','login_time','image',
    'comment_id','comment_title','comment_text',
    'option_sex','option_min_age','option_max_age','option_country','option_area','option_push_friend','option_push_chat'
);
if($sendKey === 2) {
    $url = 'http://133.242.23.29/friends/api/userUpdate.php';
    foreach($list as $key) {
        if(isset($_POST[$key]) && $_POST[$key]) {
            $data[$key] = $_POST[$key];
        }
    }
    $send = http_build_query(array('params' => json_encode($data)), "", "&");
    $header = array(
        "Content-Type: application/x-www-form-urlencoded",
        "Content-Length: ".strlen($send)
    );
    $context = array(
        "http" => array(
            "method"  => "POST",
            "header"  => implode("\r\n", $header),
            "content" => $send
        )
    );
    $body = file_get_contents($url, false, stream_context_create($context));
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
?>
<!DOCTYPE html>
<html>
    <head>
        <title>ユーザ情報更新</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>
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
                <input type="hidden" name="send" value="<?=$send === 0 ? 1 : 2?>"/>
                <input type="submit" value="送信" />
            </form>
        </div>
    </body>
</html>