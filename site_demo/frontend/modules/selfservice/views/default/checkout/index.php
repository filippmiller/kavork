<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.10.18
 * Time: 14:25
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>
<div class="self_checkout">
<div class="col-lg-10 col-lg-offset-1 col-md-12">
<div class="row">
    <div class="col-md-12 self_title"><?=Yii::t('selfservice', 'Self-Checkout');?></div>
	<div class="col-md-12"
	     style="margin-bottom:10px;text-align:center !important;line-height:36px;color:#FFFFFF;font-size:36px;font-weight: 600;">
		<?=Yii::t('selfservice', 'Payment system on bank cards');?>
	</div>
<div class="col-md-12 instruction">
	<div class="col-md-12 instruction_title">
		<?=Yii::t('selfservice','Please follow these steps to proceed to your self checkout');?>
	</div>

	<div class="col-md-12 fg-white" style="margin-bottom:10px;font-size: 22px;
    line-height: 34px;font-weight: 600;">
		<ol class="ul_styled">
			<li><?=Yii::t('selfservice','Begin to enter your name and select your record.');?></li>
			<li><?=Yii::t('selfservice','Confirm that you have chosen the correct record.');?></li>
			<li><?=Yii::t('selfservice','Press - "Pay By Card"');?></li>
			<li><?=Yii::t('selfservice','Follow instructions on the screen.');?></li>
		</ol>
	</div>
</div>

<div class="col-md-12">
	<?php $form = ActiveForm::begin([
		'id'                   => 'self-service-checkout-user-form',
		'enableAjaxValidation' => true,
		'fieldConfig'          => [
			'template'     => "{label}\n{input}\n",
			'options'      => [
				'tag'   => null,
				'class' => null,
			],
			'labelOptions' => [
				'class' => null,
			],
			'inputOptions' => [
				//'class' => null,
			],
		],
	]); ?>
	<div class="form-group">
		<?= Html::textInput('name', null, [
			'class'        => 'form-control __checkout_user_name_input__',
			'placeholder'  => Yii::t('selfservice', 'Enter your name'),
			'autocomplete' => 'off',
		]); ?>
	</div>
	<div class="col-md-12 padding-off-left padding-off-right" id="finded_visits_wrapper"></div>
	<div class="clearfix"></div>
	<hr style=" border-top: 2px solid #2a296c;width: 100%;margin-bottom:10px;">
	<div class="img_cards"></div>
	<?php ActiveForm::end(); ?>
</div>
</div>
</div>
</div>