<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model \common\models\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'login');
?>
<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => [
        'class' => 'login-form has-science-blue',
        'role' => 'form'
    ],
]); ?>

<div class="text-center margin-off">
  <div class="row">
    <div class="col-xs-3 text-left">
      <h3 class="push-down-tiny margin-off-bottom back">
        <?php if (Yii::$app->params['mainLanding']) { ?>
          <a href="/" class="hover-underline-none"><i class="icon-metro-arrow-left-3"></i></a>
        <?php } ?>
      </h3>
    </div>
    <div class="col-xs-6 text-center">
      <h2 class="fms margin-off-bottom">kavork</h2>
    </div>
  </div>
  <h6 class="push-down-margin-tiny"><?=Yii::t('landing', 'franchise management system')?></h6>
  <hr class="">
</div>

<div class="form-control-addon-fill pad">
    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
</div>

<div class="form-control-addon-fill pad">
    <?= $form->field($model, 'password')->passwordInput() ?>
</div>


<div class="form-group">
  <?= Html::submitButton(\Yii::t('app', 'login'), ['class' => 'btn btn-science-blue form-control', 'name' => 'login-button']) ?>
</div>

<?php ActiveForm::end(); ?>

