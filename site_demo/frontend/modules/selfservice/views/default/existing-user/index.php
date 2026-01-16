<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 19.10.18
 * Time: 23:42
 */

use common\components\Helper;
use kartik\typeahead\Typeahead;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$suggestion_template = Helper::in_line(Yii::t('main', 'ajax_find_line_selfservice'));

?>
<div class="existing-user">
<div class="col-md-12 title_regular">
<?= Yii::t('main', 'If you have visited us already, please check yourself-in to start the time') ?>	
	</div>
<div class="col-md-12">
	<ol class="ul_styled">
		<li><?= Yii::t('main', 'Start filling any of the fields that you entered when you first visited anticafe.') ?></li>
		<li><?= Yii::t('main', 'In the drop down list, please locate your information. Select them.') ?></li>
		<li><?= Yii::t('main', 'Make sure it is your information. Press the button "I confirm, this is my information".') ?></li>
		<!--<li> Welcome! Come inside, make yourself at home! Do not forget to pick up your drinks at the kitchen!</li>-->
	</ol>
<?php $form = ActiveForm::begin([
	'id'          => 'self-service-existing-user-form',
	'enableAjaxValidation' => true,
	'options' => [
		'autocomplete' => 'off',
	],
	'fieldConfig' => [
		'template'             => "{label}\n{input}\n",
		'options'              => [
			'tag'   => null,
			'class' => null,
		],
		'labelOptions'         => [
			'class' => null,
		],
		'inputOptions'         => [
			//'class' => null,
		],
	],
]); ?>
<?= $form->field($model, 'visitor_id')->hiddenInput(['class' => 'existing_user_visitor_id'])->label(false); ?>
    <div class="form_regular">    
	<div class="row form-group">
		<div class="col-md-12 form-group selflogin">
			<?= $form->field($model, 'name')->widget(Typeahead::class, [
				'options'      => [
					'class'        => '__existing_user_name_input__',
					'placeholder' => Yii::t('selfservice', 'Find your data'),
					'autocomplete' => 'off',
				],
				'dataset'      => [
					[
						'datumTokenizer' => "Bloodhound.tokenizers.obj.whitespace('value')",
						'display'        => 'name',
						'minLength'      => 3,
						'limit'          => 20,
						'remote'         => [
							'url'      => Url::to(['/visitor/ajax']) . '?term=%QUERY',
							'wildcard' => '%QUERY',
						],
						'templates'      => [
							'notFound'   => '<div class="text-danger" style="padding:0 8px">' . Yii::t('selfservice', 'Data not found.') . '</div>',
							'suggestion' => new JsExpression("Handlebars.compile('" . $suggestion_template . "')"),
						],
					],
				],
				'pluginEvents' => [
					'typeahead:select' => 'function(event, suggestion) { typeahead_existing_user_select(event, suggestion); }',
				],
			]); ?>
		</div>
		<div class="col-md-2 form-group" style="display:none;">
			<label for="email" style="margin-top:10px;">Email :</label>
		</div>
		<div class="col-md-10 form-group" style="display:none;">
			<?= Html::textInput('email', null, [
				'class'       => 'form-control existing_user_email',
				'placeholder' => '-',
				'disabled'    => 'disabled',
				'style' => 'background-color: #0455A8;border: 1px solid #0455A8;color:#fff;cursor:default !important;',
			]) ?>
			<div class="pp_helper dn">
				<table class="table"></table>
			</div>
		</div>

		<div class="col-md-12 margin-top-20">
			<div class="panel panel-info margin-top-10">

				<div class="panel-heading" data-toggle="collapse" href="#collapseeng" aria-expanded="false"
				     style="cursor:pointer;">
                     <div id="click_here">
				 <img src="/img/tap.svg" class="click_here" alt="click here">
				 </div>
					<div class="text-center">
							<span class="panel_title">
								<?= Yii::t('selfservice', 'Are you here with family or a friend and you want to pay for them at the end? Add them to your time-card here?') ?>
							</span>
						<div>
								<span class="panel_text">
									<?= Yii::t('selfservice', 'CLICK HERE TO ADD ADDITIONAL PEOPLE') ?>
								</span>
						</div>
					</div>
				</div>

				<?= $this->render('../_guests', [
					'form'  => $form,
					'model' => $model,
				]); ?>

			</div>

		</div>

	</div>
    <div class="row">
	<div class="col-md-6 text-left">
		<button type="reset" class="btn btn-warning btn-lg but_regular"><i
				class="fa fa-eraser"></i> <?= Yii::t('selfservice', 'Clear Form') ?>
		</button>
	</div>
	<div class="col-md-6 text-right">
		<button type="submit" class="btn btn-success btn-lg but_regular">
			<i class="icon-metro-enter"></i> <?= Yii::t('selfservice', 'I confirm, this is my information') ?>
		</button>
	</div>
	</div>
	</div>
	<style>.icon_left a {display:block;}</style>
<?php ActiveForm::end(); ?>
</div>
</div>
