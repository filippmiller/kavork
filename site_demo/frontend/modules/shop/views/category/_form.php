<?php

use frontend\modules\shop\models\ShopBaseModel;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\shop\models\ShopCategory */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="shop-category-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'cafe_id')->dropDownList(ShopBaseModel::getExtendedCafeList()); ?>

  <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>


