<?php
require_once(__DIR__."/../_header.php");
if(isset($_POST['send'])) {
    $pushId = $_POST['reg_id'];
    $con = $storage->getConnection('default');
    $stmt = $con->prepare('SELECT * FROM user WHERE push_id = ?');
    $stmt->execute(array($pushId));
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    if($user) {
        //PUSH送信
        $device = $user['device'] == \library\Model_User::DEVICE_IOS ? \library\Push::TYPE_IOS : \library\Push::TYPE_ANDROID;
        $bool = $push->send(
            $device,
            $user['push_id'],
            $push->message($_POST['message'])
            ->badge(3)
            ->sound(null)
        );
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>PUSH送信</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>
            <form method="POST">
                registration_id:<input type="text" name="reg_id" size="50" value=""/><br/>
                message:<input type="text" name="message" size="50" value="テストメッセージ"/><br/>
                <input type="hidden" name="send" value="1"/>
                <input type="submit" value="送信" />
            </form>
        </div>
    </body>
</html>