<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 19.10.18
 * Time: 16:33
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

?>


	<!--<div class="col-md-2">

		<a href="<?= Url::to(['/selfservice/default/dashboard']); ?>">
			<button type="button" class="btn btn-science-blue btn-lg crut" style="text-transform: uppercase;">
				<i class="fa fa-arrow-circle-o-left"></i> initial screen
			</button>
		</a>
	</div>-->
	<div class="new_user_form">
	<div class="col-md-12 new_user_title">
		<?= Yii::t('selfservice', 'We would like to get to know you better') ?>
	</div>
<div class="col-md-12"> 	
<ul id="steps">
	<li class="current" data-step-id="1"><?= Yii::t('selfservice', 'Step') ?> 1<span><?= Yii::t('selfservice', 'First Name') ?></span></li>
	<li data-step-id="2"><?= Yii::t('selfservice', 'Step') ?> 2<span><?= Yii::t('selfservice', 'Last name') ?></span></li>
	<li data-step-id="3"><?= Yii::t('selfservice', 'Step') ?> 3<span><?= Yii::t('selfservice', 'Email') ?></span></li>
	<li data-step-id="4"><?= Yii::t('selfservice', 'Step') ?> 4<span><?= Yii::t('selfservice', 'Number of people') ?></span></li>
	<li data-step-id="5"><?= Yii::t('selfservice', 'Step') ?> 5<span><?= Yii::t('selfservice', 'Confirmation') ?></span></li>
</ul>
<?php $form = ActiveForm::begin([
	'id'                   => 'self-service-new-user-form',
	'enableAjaxValidation' => true,
	'options'              => [
		'class' => 'step_form',
	],
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
	<div id="step_1" class="step animated" data-step-id="1">
	<fieldset class="form-group">
		<?= $form->field($model, 'first_name')->textInput(); ?>
		<div class="col-md-12 opis">
			<i class="fa fa-exclamation-triangle"></i> <?= Yii::t('selfservice', 'Required field') ?>
		</div>
		<p class="step_commands">
			<a href="#" class="next btn btn-success btn-lg">
				<?= Yii::t('selfservice', 'Next') ?> <i class="fa fa-chevron-circle-right"></i>
			</a>
		</p>
	</fieldset>
</div>

<div id="step_2" class="step animated hidden" data-step-id="2">
	<fieldset class="form-group">
		<?= $form->field($model, 'last_name')->textInput(); ?>
		<div class="col-md-12 opis_green">
			<i class="fa fa-pencil"></i> <?= Yii::t('selfservice', 'Recommended field') ?>
		</div>
		<p class="step_commands">
			<a href="#" class="prev btn btn-tree-poppy btn-lg">
				<i class="fa fa-chevron-circle-left"></i> <?= Yii::t('selfservice', 'Back') ?>
			</a>
			<a href="#" class="next btn btn-success btn-lg">
				<?= Yii::t('selfservice', 'Next') ?> <i class="fa fa-chevron-circle-right"></i>
			</a>
		</p>
	</fieldset>
</div>

<div id="step_3" class="step animated hidden" data-step-id="3">
	<fieldset class="form-group">
		<?= $form->field($model, 'email')->textInput(); ?>
		<div class="col-md-12 opis_green">
			<i class="fa fa-pencil"></i>  <?= Yii::t('selfservice', 'RECOMMENDED TO FILL THE FIELD. YOUR DATA IS PROTECTED') ?>
			<div class="text_trees">
				<?= Yii::t('selfservice', 'We like to have your email so we can easier identify you for your next visit and to send you electronic receipt.') ?>
			</div>
		</div>
		<p class="step_commands">
			<a href="#" class="prev btn btn-tree-poppy btn-lg">
				<i class="fa fa-chevron-circle-left"></i> <?= Yii::t('selfservice', 'Back') ?>
			</a>
			<a href="#" class="next btn btn-success btn-lg">
				<?= Yii::t('selfservice', 'Next') ?> <i class="fa fa-chevron-circle-right"></i>
			</a>
		</p>
	</fieldset>
</div>

	<div id="step_4" class="step animated hidden" data-step-id="4">
	<fieldset class="form-group">

		<div class="panel panel-info margin-off-bottom">
			<div class="panel-heading" data-toggle="collapse" href="#collapseeng"
			     aria-expanded="false">
				 <div id="click_here">
				 <img src="/img/tap.svg" class="click_here" alt="click here">
				 </div>
				<div class="text-center">
					<span class="fam_text"><?= Yii::t('selfservice', 'Are you here with
						family or a friend and you want to pay for them at the end? Add them to your time-card
						here?') ?></span>
					<div class="raz_fam">
						<span class="adit_people"><?= Yii::t('selfservice', 'CLICK HERE TO ADD ADDITIONAL PEOPLE') ?></span>
					</div>
				</div>
			</div>

			<?= $this->render('../_guests', [
				'form'  => $form,
				'model' => $model,
			]); ?>

		</div>

		<div class="col-md-12 opis dang_text">
			<i class="fa fa-exclamation-triangle"></i> <?= Yii::t('selfservice', 'Attention. If you come without company, then skip this step,
			neither of which do not fill') ?>
		</div>
		<p class="step_commands">
			<a href="#" class="prev btn btn-tree-poppy btn-lg">
				<i class="fa fa-chevron-circle-left"></i> <?= Yii::t('selfservice', 'Back') ?>
			</a>
			<a href="#" class="next btn btn-success btn-lg">
				<?= Yii::t('selfservice', 'Next') ?> <i class="fa fa-chevron-circle-right"></i>
			</a>
		</p>
	</fieldset>
</div>

	<div id="step_5" class="step animated hidden" data-step-id="5">
		<fieldset class="form-group">
			<div class="marg-bottom-min85">
				<div class="col-md-12 opis_white"><?= Yii::t('selfservice', 'Last step') ?></div>
				<div class="col-md-8 col-md-offset-4 text-left push-down-thin">
					<span id="result1" class="result">
						<div class="col-md-3 padding-off-right"><?= Yii::t('selfservice', 'Name') ?> &nbsp;&nbsp;:</div>
						<div class="col-md-9 padding-off-left _report_full_name"></div>
						<br>
					</span>
					<span id="result3" class="result">
						<div class="col-md-3 padding-off-right"><?= Yii::t('selfservice', 'Email') ?> &nbsp;&nbsp;:</div>
						<div class="col-md-9 padding-off-left _report_email"></div>
						<br>
					</span>
					<span id="result4" class="result">
						<div class="col-md-3 padding-off-right"><?= Yii::t('selfservice', 'Person') ?> :</div>
						<div class="col-md-9 padding-off-left _report_persons"><?= Yii::t('selfservice', '1 (you)') ?></div>
					</span>
					<span id="result5" class="result"></span>
				</div>
				<div class="col-md-12 opis_white font-size-54">
					<i class="fa fa-caret-down"></i>
				</div>
				<button type="submit" class="btn btn-success btn-lg" style="text-transform: uppercase; display: inline-block;"><?= Yii::t('selfservice', 'Please confirm information is valid.') ?></button>
			</div>
			<p class="step_commands">
				<a href="#" id="step4Prev" class="prev btn btn-tree-poppy btn-lg">
					<i class="fa fa-chevron-circle-left"></i> <?= Yii::t('selfservice', 'Back') ?>
				</a>
			</p>
		</fieldset>
	</div>
</div>
</div>
<style>.icon_left a {display:block;}</style>
<?php ActiveForm::end(); ?>