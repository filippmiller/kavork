<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\color\ColorInput;
use frontend\modules\users\models\Users;
use kartik\time\TimePicker;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model frontend\modules\users\models\UserLog */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="user-log-form">

    <?php $form = ActiveForm::begin(); ?>


  <?= $form->field($model, 'start_date')->widget(DatePicker::class, [
      'convertFormat' => true,
      'pluginOptions' => [
          'autoclose' => true,
          'format'    => 'php:' . Yii::$app->params['lang']['date'],
      ],
  ]); ?>

  <?= $form->field($model, 'start_time')->widget(TimePicker::class, [
      'pluginOptions' => [
          'showMeridian' => !Yii::$app->params['lang']['time24Hour'],
          'showSeconds'  => Yii::$app->params['lang']['timeShowSeconds'],
		  'minuteStep' => 1,
      ],
  ]); ?>

  <?php if($model->finish){;?>
    <?= $form->field($model, 'finish_date')->widget(DatePicker::class, [
        'convertFormat' => true,
        'pluginOptions' => [
            'autoclose' => true,
            'format'    => 'php:' . Yii::$app->params['lang']['date'],
        ],
    ]); ?>

    <?= $form->field($model, 'finish_time')->widget(TimePicker::class, [
        'pluginOptions' => [
            'showMeridian' => !Yii::$app->params['lang']['time24Hour'],
            'showSeconds'  => Yii::$app->params['lang']['timeShowSeconds'],
			'minuteStep' => 1,
        ],
    ]); ?>
  <?php };?>

  <?php
    //$form->field($model, 'cafe_id')->dropDownList(\yii\helpers\ArrayHelper::map((array)Users::getCafesList(), 'id', 'name'))
  ?>

    <?php if(!$isAjax){?>
      <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
      </div>
    <?php }?>
    <?php ActiveForm::end(); ?>

</div>


