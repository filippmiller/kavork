<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\shop\models\ShopTransaction */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="shop-transaction-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'operation_type_id')->textInput() ?>

  <?= $form->field($model, 'product_id')->textInput() ?>

  <?= $form->field($model, 'sale_id')->textInput() ?>

  <?= $form->field($model, 'quantity')->textInput() ?>

  <?= $form->field($model, 'price')->textInput() ?>

  <?= $form->field($model, 'vat')->textInput() ?>

  <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

  <?= $form->field($model, 'updated_by')->textInput() ?>

  <?= $form->field($model, 'updated_at')->textInput() ?>

  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>


