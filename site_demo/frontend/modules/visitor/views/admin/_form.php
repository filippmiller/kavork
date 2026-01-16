<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\visitor\models\Visitor */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="visitor-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'f_name')->textInput() ?>

  <?= $form->field($model, 'l_name')->textInput() ?>
  
 <div class="row">
 <div class="col-md-6">
 <?= $form->field($model, 'lg')->dropDownList(Yii::$app->cafe->languageList) ?>
</div>
  <div class="col-md-6">
  <?= $form->field($model, 'email')->textInput() ?>
  </div>
  <div class="col-md-6">
  <?= $form->field($model, 'phone')->textInput() ?>
  </div>
  <div class="col-md-6">
 <?= $form->field($model, 'code')->textInput() ?>
 </div>
</div>
  <?php if (Yii::$app->user->can('AllFranchisee')) {
    echo $form->field($model, 'franchisee_id')->dropDownList(\frontend\modules\franchisee\models\Franchisee::getList());
  } ?>
   <?= $form->field($model, 'notice')->textarea(['rows' => 4]) ?>
  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>


