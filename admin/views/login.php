<?php require_once('/home/homepage/html/public/friends/admin/inc/define.php');?>
<?php
$command = isset($_POST['command']) ? $_POST['command'] : null;
$error = "";
if($command === 'login') {
    //登録処理
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    if(!empty($id) && !empty($password)) {
        $admin = $storage->Administrator->primaryOne($id);
        if(!empty($user)) {
            $hash = $user['password'];
            if(\library\Password::verify($password, $hash)) {
                $_SESSION['admin'] = $id;
                return redirect('index.php');
            }
        } else {
            $error =  "<br/>・入力内容に不正があります<br/>";
        }
    } else {
        if(!$id) {
            $error .= '<br/>・User IDを入力してください。';
        }
        if(!$password) {
            $error .= '<br/>・Passwordを入力してください。';
        }
    }
}
?>
<!DOCTYPE html>
<html class="bg-black">
    <head>
        <meta charset="UTF-8">
        <title>AdminLTE | Log in</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link href="<?=\library\Assets::uri('bootstrap.min.css','css')?>" rel="stylesheet" type="text/css" />
        <link href="<?=\library\Assets::uri('font-awesome.min.css','css')?>" rel="stylesheet" type="text/css" />
        <link href="<?=\library\Assets::uri('AdminLTE.css','css')?>" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <style>
        .header {
            font-size: 20px;
            font-family: 'Kaushan Script', cursive;
            font-weight: 500;
        }
        </style>
    </head>
    <body class="bg-black">

        <div class="form-box" id="login-box">
            <div class="header"><span class="logo">MAKE TALK <small>administration</small></span></div>
            <form method="post">
                <div class="body bg-gray">
                    <?php if($error):?>
                    <div class="alert alert-danger alert-dismissable" style="margin-top:12px">
                        <i class="fa fa-ban"></i>
                        <b>Error!!</b>
                        <?=$error?>
                    </div>
                    <?php endif?>
                    <div class="form-group">
                        <input type="text" name="id" class="form-control" placeholder="User ID"/>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password"/>
                    </div>
                </div>
                <div class="footer">
                    <input type="hidden" name="command" value="login" />
                    <button type="submit" class="btn bg-olive btn-block">Sign me in</button>  
                </div>
            </form>
        </div>


        <!-- jQuery 2.0.2 -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="<?=\library\Assets::uri('bootstrap.min.js','js')?>" type="text/javascript"></script>        
    </body>
</html>