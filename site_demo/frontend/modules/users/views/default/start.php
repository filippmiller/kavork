<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>


<div class="session-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'user_id')->dropDownList(\yii\helpers\ArrayHelper::map(Yii::$app->cafe->getCafeUsersList(), 'id', 'name')) ?>

  <?php
  if (Yii::$app->cafe->can('sessionStartPasswordRequest')) {
    echo $form->field($model, 'password')->passwordInput();
  }
  ?>

  <?php ActiveForm::end(); ?>

</div>
