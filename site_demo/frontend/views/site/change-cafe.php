<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

/* @var $model \common\models\LoginForm */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Change cafe');
$isModal = (isset($isModal) && $isModal);
$cafe_change = (isset($cafe_change) ? $cafe_change : 0);
?>

<?php $form = ActiveForm::begin([
    'id' => $isModal ? "cafe_change" : 'login-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'input-group',
        ]
    ],
    'options' => [
        'class' => $isModal ? "cafe_change has-science-blue" : 'login-form has-science-blue',
        'role' => 'form'
    ],
]); ?>
<?php if (isset($cafe)) { ?>
  <div class="text-center margin-off">
    <h2 class="fms margin-off-bottom">kavork</h2>
    <h6 class="push-down-margin-tiny"><?=Yii::t('landing', 'franchise management system')?></h6>
    <hr class="has-science-blue">
  </div>
<?php } ?>
<div class="form-control-addon-fill pad">
 <label><?=Yii::t('app', 'Select the department you will enter now')?></label>
  <div class="input-group">
    <span class="input-group-addon fg-white"><i class="fa fa-home"></i></span>
    <select class="form-control" name="cafe">
      <?php foreach ($cafe_list as $cafe) { ?>
        <option
            value="<?= $cafe['id']; ?>" <?= ($cafe['id'] == $cafe_change) ? "selected" : ""; ?>><?= isset($cafe['name']) ? $cafe['name'] : $cafe->cafe['name']; ?></option>
      <?php } ?>
    </select>
  </div>
</div>


<?php if (!$isModal) { ?>
  <div class="form-group padding-top-10 margin-top-10">
    <?= Html::submitButton(\Yii::t('app', 'Enter'), [
        'class' => 'btn btn-science-blue form-control',
        'name' => 'login-button',
        'value' => 1,
    ]) ?>
  </div>
  <?php if (Yii::$app->user->can('SelfServiceModeEnter')): ?>
  <hr>
    <div class="form-group">
	<div class="self_screen_min">
	  <?=Html::submitButton('<span class="icon-metro-screen"></span>&nbsp;&nbsp;'. Yii::t('app', 'Self Service') .'', [
          'class' => 'btn btn-neutral-border form-control',
          'name' => 'selfservice',
          'value' => 1,
      ]) ?>
	</div>  
    </div>
	</div>
  <?php endif; ?>
<?php } ?>

<?php ActiveForm::end(); ?>

