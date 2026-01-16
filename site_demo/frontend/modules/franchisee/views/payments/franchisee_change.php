<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\franchisee\models\FranchiseePayments */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="franchisee-payments-form">

  <?php $form = ActiveForm::begin(); ?>


  <div class="form-group field-franchiseepayments-franchisee_id required">
    <label class="control-label" for="franchiseepayments-franchisee_id">
      <?= $model->getAttributeLabel('franchisee_id'); ?>
    </label>
    <?php if (Yii::$app->user->can('AllFranchisee')) {
      echo Html::dropDownList('franchisee_id', $model->franchisee_id, $franchisee, [
          'class' => 'form-control'
      ]);
    } ?>
  </div>


  <?php ActiveForm::end(); ?>

</div>


