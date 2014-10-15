<?php
require_once(__DIR__."/../_header.php");
$sendKey = filter_input(INPUT_POST,'send');
/*
if($sendKey) {
    $url = "http://133.242.237.100/api/movie/purchased";
    $data['id'] = 12;
    $send = http_build_query($data, "", "&");
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
    var_dump(json_decode($body,true));
}
 * */
?>
<!DOCTYPE html>
<html>
    <head>
        <title>オレンジ</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>
            <form method="POST" action="http://133.242.237.100/api/movie/log">
                <input type="hidden" name="send" value="1"/>
                <input type="submit" value="送信" />
            </form>
        </div>
    </body>
</html>