<?php
$_cssList = isset($_cssList) ? $_cssList : array();
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <title>MAKE TALK - [admin page]</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <!-- bootstrap 3.0.2 -->
    <link href="<?=\library\Assets::uri('bootstrap.min.css','css')?>" rel="stylesheet" type="text/css" />
    <!-- font Awesome -->
    <link href="<?=\library\Assets::uri('font-awesome.min.css','css')?>" rel="stylesheet" type="text/css" />
    <!-- Ionicons -->
    <link href="<?=\library\Assets::uri('ionicons.min.css','css')?>" rel="stylesheet" type="text/css" />
    <?php foreach ($_cssList as $path):?>
    <link href="<?=\library\Assets::uri($path,'css')?>" rel="stylesheet" type="text/css" />
    <?php endforeach ?>
    <!-- Theme style -->
    <link href="<?=\library\Assets::uri('AdminLTE.css','css')?>" rel="stylesheet" type="text/css" />
    <!-- original style -->
    <link href="<?=\library\Assets::uri('style.css','css')?>" rel="stylesheet" type="text/css" />
</head>
<style>
.logo{
  width:250px!important;
}
body > .header .navbar {
  margin-left:250px!important;
}
li.treeview {
  margin: 0;
  padding: 0;
  width:200px;
  border-left: 1px solid #fff;
  /*border-right: 1px solid #dbdbdb;*/
  float:right;
}
li.treeview > a {
  padding: 12px 5px 12px 15px;
  display: block;
}
li.treeview > a > .fa,
li.treeview > a > .glyphicon,
li.treeview > a > .ion {
  width: 20px;
}
.skin-blue .treeview-menu {
  background-color:#6ED25A;
}
li.treeview .treeview-menu {
  display: none;
  list-style: none;
  padding: 0;
  margin: 0;
  position:absolute;
  width:100%;
}
li.treeview .treeview-menu > li {
  margin: 0!important;
}
li.treeview .treeview-menu > li > a {
  padding: 5px 5px 5px 15px;
  margin:0!important;
  display: block;
  font-size: 14px;
  margin: 0px 0px;
}
li.treeview .treeview-menu > li > a > .fa,
li.treeview .treeview-menu > li > a > .glyphicon,
li.treeview .treeview-menu > li > a > .ion {
  width: 20px;
}
</style>
<body class="skin-blue">
    <header class="header">
        <a href="<?=\library\Assets::uri('/index.php')?>" class="logo">
            MAKE TALK <small>administration</small>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top" role="navigation">
            <div class="navbar-right" style="padding-top:3px;">
                <ul class="nav navbar-nav">
                    <form method="GET" action="<?=\library\Assets::uri('data/detail.php')?>" style="width:200px;float:right;padding:4px 4px 6px;border-left: 1px solid #fff;">
                        <div class="input-group">
                        <span class="input-group-addon"><i class="ion ion-person"></i></span>
                        <input type="text" name="id" class="form-control" style="width:150px;" />
                        </div>
                    </form>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-folder"></i> <span>集計</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?=\library\Assets::uri('statistics/regist_user_daily.php')?>"><i class="fa fa-angle-double-right"></i> 登録ユーザ数(日別)</a></li>
                            <li><a href="<?=\library\Assets::uri('statistics/uu_daily.php')?>"><i class="fa fa-angle-double-right"></i> UU(日別)</a></li>
                        </ul>
                    </li>
                    <li class="treeview">
                        <a href="#">
                            <i class="fa fa-folder"></i> <span>データ</span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li><a href="<?=\library\Assets::uri('data/index.php')?>"><i class="fa fa-angle-double-right"></i> ユーザ検索</a></li>
                            <li><a href="<?=\library\Assets::uri('data/search_message.php')?>"><i class="fa fa-angle-double-right"></i> 会話検索</a></li>
                            <li><a href="<?=\library\Assets::uri('data/search_report.php')?>"><i class="fa fa-angle-double-right"></i> 違反報告検索</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
        
    