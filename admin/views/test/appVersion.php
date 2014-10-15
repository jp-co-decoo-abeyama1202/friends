<?php
require_once(__DIR__ . '/../../inc/define.php');
list($code,$body) = apiTest(
    \library\Assets::uri('appVersion.php','test_api'),array()
);
require_once(__DIR__ . '/../_header.php');
?>
<aside class="right-side">
<!-- Main content -->
    <section class="content-header">
        <h1>アプリバージョン取得</h1>
        <ol class="breadcrumb">
            <li><a href="<?=\library\Assets::uri('/')?>"><i class="fa fa-dashboard"></i>Dashboard</a></li>
            <li><a href="<?=\library\Assets::uri('/test/')?>">API一覧</a></li>
            <li class="active">アプリバージョン取得</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title"><i class="ion ion-ios7-search-strong"> 取得内容</i></h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="return_code">HTTP Response code</label>
                            <input type="text" value="<?=$code?>" id="return_code" class="form-control" readonly/>
                        </div>
                        <div class="form-group">
                            <label for="return_body">Body</label>
                            <textarea id="return_body" rows="20" class="form-control" readonly><?=$body?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</aside>
<?php
require_once(__DIR__ . '/../_footContent.php');
require_once(__DIR__ . '/../_footer.php');