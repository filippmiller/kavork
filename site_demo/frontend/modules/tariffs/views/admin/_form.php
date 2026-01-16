<?php

use frontend\modules\tariffs\models\Tariffs;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\tariffs\models\Tariffs */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="tariffs-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'type_id')->dropDownList(Tariffs::getTypeLabels()) ?>

  <?php if (Tariffs::test(false)){?>
  	<?= $form->field($model, 'start_visit')->textInput() ?>
  <?php };?>

   <div class="row">
    <div class="col-md-6"><?= $form->field($model, 'min_sum')->textInput() ?></div>
    <div class="col-md-6"><?= $form->field($model, 'max_sum')->textInput() ?></div>
   </div>
    <?= $form->field($model, 'first_hour')->textInput() ?>

    <?= $this->render('_form_hours', [
        'form'  => $form,
        'model' => $model,
    ]); ?>


    <?= $form->field($model, 'active')->dropDownList([
        0 => Yii::t('app', 'Active'),
        1 => Yii::t('app', 'Blocked'),
    ]) ?>

    <?php if(!$isAjax){?>
      <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
      </div>
    <?php }?>
    <?php ActiveForm::end(); ?>

</div>


