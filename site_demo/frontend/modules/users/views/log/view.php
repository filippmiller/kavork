<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>
<div class="session-form">
  <h6><?=Yii::t('app','Name');?>: <?=$model->user->name;?></h6>

  <h6><?=Yii::t('app','Last {n} day duration',['n'=>7]);?>: <?=Yii::$app->helper->echo_time($model->user->getDuration('7 day'));?></h6>

  <h6><?=Yii::t('app','Last {n} day duration',['n'=>30]);?>: <?=Yii::$app->helper->echo_time($model->user->getDuration('30 day'));?></h6>

  <h6><?=Yii::t('app','Last month duration');?>: <?=Yii::$app->helper->echo_time($model->user->getDuration('1 month'));?></h6>

  <h6><?=Yii::t('app','All duration');?>: <?=Yii::$app->helper->echo_time($model->user->getDuration());?></h6>

  <h6><?=Yii::t('app','This session start');?>: <?=date(Yii::$app->params['lang']['datetime'],strtotime($model->start));?></h6>

  <h6><?=Yii::t('app','This session duration');?>: <?=Yii::$app->helper->echo_time($model->duration);?></h6>

  <?php $form = ActiveForm::begin(); ?>
       <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
  <?php
  if (Yii::$app->user->can('UserLogSessionStopWithoutPassword')) {
    // No need to request password
  } else if (Yii::$app->cafe->can('sessionStopPasswordRequest')) {
    echo $form->field($model, 'password')->passwordInput();
  }
  ?>

  <?php ActiveForm::end(); ?>

</div>
