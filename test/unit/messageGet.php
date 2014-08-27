<?php
$messages = array();
if(isset($_POST['send'])) {
    $url = 'http://133.242.23.29/friends/api/messageGet.php';
    $list = array('udid','friend_id','offset','count');
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
        <title>メッセージ取得</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            th,td {border:1px solid black;}
        </style>
    </head>
    <body>
        <div>
            <?php foreach($messages['message'] as $message):?>
            <table style="border:1px solid black;margin-bottom:5px;">
                <tr>
                    <th>ID:<?=$message['id']?></th><th>送信者:<?=$message['sender_id']?></th><td><?=$message['message']?></td><td><?=date('Y/m/j H:i:s',$message['create_time'])?></td>
                </tr>
            </table>
            <?php endforeach ?>
            <form method="POST">
                from_id:<input type="text" name="udid" size="50" value="00000000000000000000000000000000"/><br/>
                to_id:<input type="text" name="friend_id" size="50" value="00000000000000000000000000000001"/><br/>
                offset:<input type="text" name="offset" size="10" value="0"/>　count:<input type="text" name="count" size="10" value="30"/><br/>
                <input type="hidden" name="send" value="1"/>
                <input type="submit" value="送信" />
            </form>
        </div>
    </body>
</html>