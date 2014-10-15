<?php
$messages = array();
if(isset($_POST['send'])) {
    $url = 'http://133.242.23.29/friends/api/messageNewList.php';
    $list = array('user_id');
    $data = array();
    foreach($list as $key) {
        $data[$key] = $_POST[$key];
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
    echo $body."<br/>";
    $messages = json_decode($body,true);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>新着メッセージ一覧</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            th,td {border:1px solid black;}
        </style>
    </head>
    <body>
        <div>
            <?php if($messages): ?>
                <table style="border:1px solid black;margin-bottom:5px;">
                    <tr>
                        <th>受信者</th><td><?=$messages['user_id']?></td>
                    </tr>
                    <tr>
                        <th>未読数</th><td><?=$messages['no_read']?></td>
                    </tr>
                     <tr>
                        <th>申請数</th><td><?=$messages['request']?></td>
                    </tr>
                </table>
                <?php foreach($messages['list'] as $message):?>
                <table style="border:1px solid black;margin-bottom:5px;">
                    <tr>
                        <th>ID:<?=$message['id']?></th>
                        <th>送信者:<?=$message['sender']?></th>
                        <td><?=$message['message']?></td>
                        <td><?=date('Y/m/j H:i:s',$message['create_time'])?></td>
                        <th>フレンド情報</th>
                        <td><?=var_export($message['friend'])?></td>
                    </tr>
                </table>
                <?php endforeach ?>
            <?php endif?>
            <form method="POST">
                from_id:<input type="text" name="user_id" size="50" value="00000000000000000000000000000000"/><br/>
                <input type="hidden" name="send" value="1"/>
                <input type="submit" value="送信" />
            </form>
        </div>
    </body>
</html>