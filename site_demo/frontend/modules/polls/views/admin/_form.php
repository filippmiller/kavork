<?php

use frontend\modules\polls\models\Polls;
use unclead\multipleinput\MultipleInput;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\polls\models\Polls */
/* @var $form yii\bootstrap\ActiveForm */

$columns = [
    [
        'name' => 'id',
        'type' => \unclead\multipleinput\TabularColumn::TYPE_HIDDEN_INPUT
    ],
    [
        'name' => 'answers',
    ],
];

?>

<div class="polls-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'question')->textarea(['rows' => 4]) ?>

  <?= $form->field($model, 'answers')->widget(MultipleInput::className(), [
      'id' => 'poll_answers',
      'max' => 100,
      'min' => 0, // should be at least 2 rows
      'allowEmptyList' => true,
      'enableGuessTitle' => true,
      'sortable' => true,
      'cloneButton' => true,
      'attributeOptions' => [
          'enableAjaxValidation' => false,
          'enableClientValidation' => true,
          'validateOnChange' => false,
          'validateOnSubmit' => true,
          'validateOnBlur' => false,
      ],
      'addButtonPosition' => MultipleInput::POS_HEADER, // show add button in the header
	  'addButtonOptions' => [
	  'label' => '<i class="fa fa-plus"></i><span class="text-capitalize"> '. Yii::t('config', 'create') .'</span>',
	  'class' => 'btn btn-default addbutmultiple',
	  ],
      'columns' => $columns,
  ])->label(Yii::t('app', 'Answers')); ?>

  <div class="row">
    <div class="col-md-6">
      <?= $form->field($model, 'user_status')->dropDownList(Polls::getUserStatus()) ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'event')->dropDownList(Polls::getEvents()) ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6">
      <?= $form->field($model, 'other_ans')->dropDownList(Polls::getOtherAns()) ?>
    </div>
    <div class="col-md-6">
      <?= $form->field($model, 'status')->dropDownList(Polls::getStatus()) ?>
    </div>
  </div>
  <?php $form->field($model, 'is_poll')->textInput() ?>

  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>


