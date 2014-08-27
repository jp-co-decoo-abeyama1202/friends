<?php
ini_set( 'display_errors', 1 );
$stateList = array(
    1 => '申請中',
    2 => '許可',
    3 => '却下',
    4 => '取り消し'
);
$type = isset($_GET['type']) ? $_GET['type'] : 'from';
$udid = '00000000000000000000000000000000';
$url = 'http://133.242.23.29/friends/api/friendRequestList.php';
$data = array('udid'=>$udid,'type'=>$type);
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
$list = file_get_contents($url, false, stream_context_create($context));
echo $list."<br/>";
//echo "Header:".$http_response_header[0]."<br/>";
$requestList = json_decode($list,true);
$requestList = $requestList ? $requestList : array();
if(isset($_POST['send'])) {
    $url = 'http://133.242.23.29/friends/api/messagePost.php';
    $list = array('friends_id','udid','friend_id','message');
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
        <title>リクエスト送信履歴</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            th,td{border:1px solid black;}
            
        </style>
    </head>
    <body>
        <table>
            <thead><tr>
                    <th>name</th>
                    <th>state</th>
                    <th>message</th>
            </tr></thead>
            <?php foreach($requestList as $r):?>
            <tr>
                <td><?= $r['user']['name']?></td>
                <td><?= $stateList[$r['state']]?></td>
                <td><?= $r['message']?></td>
            </tr>
            <?php endforeach ?>
        </table>
    </body>
</html>