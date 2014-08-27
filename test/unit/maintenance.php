<?php
if(isset($_POST['send'])) {
    $url = 'http://133.242.23.29/friends/api/maintenance.php';
    $send = http_build_query(array(), "", "&");
    $header = array(
        "Content-Type: application/x-www-form-urlencoded",
        "Content-Length: ".strlen($send)
    );
    $context = array(
        "http" => array(
            "method"  => "POST",
            "header"  => implode("\r\n", $header),
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
        <title>メンテチェック</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>
            <form method="POST">
                <input type="hidden" name="send" value="1"/>
                <input type="submit" value="送信" />
            </form>
        </div>
    </body>
</html>