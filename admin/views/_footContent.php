<?php
$_jsList = isset($_jsList) ? $_jsList : array();
?>
<!-- jQuery 2.0.2 -->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
<!-- jQuery UI 1.10.3 -->
<script src="<?=\library\Assets::uri('jquery-ui-1.10.3.min.js','js')?>" type="text/javascript"></script>
<!-- Bootstrap -->
<script src="<?=\library\Assets::uri('bootstrap.min.js','js')?>" type="text/javascript"></script>
<?php foreach ($_jsList as $path):?>
<script src="<?=\library\Assets::uri($path,'js')?>" type="text/javascript"></script>
<?php endforeach ?>
<!-- AdminLTE App -->
<script src="<?=\library\Assets::uri('AdminLTE/app.js','js')?>" type="text/javascript"></script>