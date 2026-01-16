<?php

use frontend\modules\users\models\Users;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\mails\models\MailsLog */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="mails-log-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'cteated_at')->textInput() ?>

  <?= $form->field($model, 'last_visitor_id')->textInput() ?>

  <?= $form->field($model, 'mail_id')->textInput() ?>

  <?= $form->field($model, 'user_id')->textInput() ?>

  <?= $form->field($model, 'cafe_id')->dropDownList(\yii\helpers\ArrayHelper::map((array)Users::getCafesList(), 'id', 'name')) ?>

  <?= $form->field($model, 'count')->textInput() ?>

  <?= $form->field($model, 'content')->textarea(['rows' => 6]) ?>

  <?= $form->field($model, 'params')->textarea(['rows' => 6]) ?>

  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>


