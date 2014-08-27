<?php
if(isset($_POST['send'])) {
    $url = 'http://133.242.23.29/friends/api/friendRequest.php';
    $list = array('from_id','to_id','text');
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
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>フレンド申請</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>
            <form method="POST">
                from_id:<input type="text" name="from_id" size="50" value="00000000000000000000000000000000"/><br/>
                to_id:<input type="text" name="to_id" size="50" value="00000000000000000000000000000001"/><br/>
                message:<input type="text" name="text" size="50" value="test" />
                <input type="hidden" name="send" value="1"/>
                <input type="submit" value="送信" />
            </form>
        </div>
    </body>
</html>