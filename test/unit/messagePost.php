<?php
if(isset($_POST['send'])) {
    $url = 'http://133.242.23.29/friends/api/messagePost.php';
    $list = array('user_id','friend_id','message');
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
    echo "BODY:".$body."<br/><hr/>";
} else {
    $_POST['user_id'] = 'user_token';
    $_POST['friend_id'] = 'friend_token';
    $_POST['message'] = 'send message';
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>メッセージ送信</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>
            <form method="POST">
                from_id:<input type="text" name="user_id" size="50" value="<?=$_POST['user_id']?>"/><br/>
                to_id:<input type="text" name="friend_id" size="50" value="<?=$_POST['friend_id']?>"/><br/>
                message:<input type="text" name="message" size="50" value="<?=$_POST['message']?>"/><br/>
                <input type="hidden" name="send" value="1"/>
                <input type="submit" value="送信" />
            </form>
        </div>
    </body>
</html>