<?php

use common\components\Helper;
use yii\bootstrap\ActiveForm;

$suggestion_template = Helper::in_line(Yii::t('main', 'ajax_find_line_selfservice'));

?>
<div class="existing-user">
<div class="col-md-12 self_title margin-bottom-10"><?=Yii::t('selfservice', 'QR_title');?></div>
    <div class="col-md-12 title_regular margin-top-10">
        <?= Yii::t('main', 'qrcode_description') ?>
    </div>
    <div class="col-md-12 text-center margin-top-10 padding-top-10">
       <!-- <ol class="ul_styled">
            <li><?= Yii::t('main', 'Scan Qr-code.') ?></li>
        </ol>-->
		<i class="glyphicon glyphicon-qrcode" style="font-size:82px;"></i>
        <?php $form = ActiveForm::begin([
            'id' => 'self-service-existing-user-form',
            'enableAjaxValidation' => true,
            'options' => [
                'autocomplete' => 'off',
            ],
            'fieldConfig' => [
                'template' => "{input}",
            ],
        ]); ?>
        <?= $form->field($model, 'code')->textInput(['class'=>'existing_user_code'])->label(false); ?>

        <style>.icon_left a {
                display: block;
            }</style>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<style>
.icon_left{display:none;}
</style>

