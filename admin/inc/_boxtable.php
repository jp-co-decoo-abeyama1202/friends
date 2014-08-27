<?php
$_boxId = isset($_boxId) ? $_boxId : '';
$_boxClass = isset($_boxClass) ? $_boxClass : '';
$_tableId = isset($_tableId) ? $_tableId : '';
$_tableIcon = isset($_tableIcon) ? $_tableIcon : '';
$_tableTitle = isset($_tableTitle) ? $_tableTitle : '';
$_tableHeader = isset($_tableHeader) ? $_tableHeader : array();
$_tableData = isset($_tableData) ? $_tableData : array();
$_errorMessage = isset($_errorMessage) ? $_errorMessage : '';
?>
<div class="box <?=$_boxClass ? $_boxClass:''?>" id="<?=$_boxId?>">
    <div class="box-header">
        <h3 class="box-title"><?=$_tableIcon ? '<i class="'.$_tableIcon.'"></i>' : ''?> <?=escapetext($_tableTitle)?></h3>
    </div>
    <div class="box-body">
        <?php if(!$_tableData):?>
        <div class="alert alert-warning alert-dismissable">
            <i class="fa fa-warning"></i>
            <b><?=escapetext($_errorMessage)?></b>
        </div>
        <?php else:?>
        <table id="<?= $_tableId?>" class="table table-bordered">
            <?php if($_tableHeader):?>
            <thead>
                <tr>
                    <?php foreach($_tableHeader as $column):
                        $_params = '';
                        $_escape = true;
                        $_value = $column;
                        ?>
                        <?php if(is_array($column)):
                            $_params = isset($column['params']) ? $column['params'] : $_params;
                            $_escape = isset($column['escape']) ? $column['escape'] : $_escape;
                            $_value = isset($column['value']) ? $column['value'] : $_value;
                        endif;?>
                    <th <?= $_params?>><?= $_escape ? escapetext($_value) : $_value?></th>
                    <?php endforeach ?>
                </tr>
            </thead>
            <?php endif?>
            <tbody>
                <?php foreach($_tableData as $data):?>
                <tr>
                    <?php foreach($data as $_key => $column):
                        $_params = '';
                        $_escape = true;
                        $_value = $column;
                        ?>
                        <?php if(is_array($column)):
                            $_params = isset($column['params']) ? $column['params'] : $_params;
                            $_escape = isset($column['escape']) ? $column['escape'] : $_escape;
                            $_value = isset($column['value']) ? $column['value'] : $_value;
                        endif;?>
                    <td <?= $_params?>><?= $_escape ? escapetext($_value) : $_value?></td>
                    <?php endforeach ?>
                </tr>
                <?php endforeach ?>
            </tbody>
            <?php if($_tableHeader):?>
            <tfoot>
                <tr>
                    <?php foreach($_tableHeader as $column):$_params = '';
                        $_escape = true;
                        $_value = $column;
                        ?>
                        <?php if(is_array($column)):
                            $_params = isset($column['params']) ? $column['params'] : $_params;
                            $_escape = isset($column['escape']) ? $column['escape'] : $_escape;
                            $_value = isset($column['value']) ? $column['value'] : $_value;
                        endif;?>
                    <th <?= $_params?>><?= $_escape ? escapetext($_value) : $_value?></th>
                    <?php endforeach ?>
                </tr>
            </tfoot>
            <?php endif?>
        </table>
        <?php endif ?>
    </div>
</div>