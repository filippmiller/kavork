<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\polls\models\Polls */
/* @var $form yii\bootstrap\ActiveForm */

$variant = $model->getAllowANS();

if (!empty($variant)) {
  $variant[-1] = Yii::t('app','Other answer');
}

$js = <<<JS
$('#pollsans-ans input').on('change', function() {
    var el = $(this);
    var text = $('.__other_ans_text');
    
    if (el.val() == -1) {
        text.removeClass('hidden');
    } else {
        text.addClass('hidden');
    }
})
JS;

$this->registerJs($js);

?>

<div class="polls-form">

  <?php $form = ActiveForm::begin(); ?>

  <?=$form->field($model,'poll_id')->hiddenInput()->label(false);?>

  <div class="qest_pol text-center font-weight-700"><?= str_replace("\n",'<br>',$model->question); ?></div>

  <?php
  $field = $form
      ->field($model, 'ans')
      ->label(false);
  $field->template = "{label}\n{error}\n{input}";
  echo $field->radioList($variant);
  ?>

  <div class="col-sm-12">
  <?php
    if($model->poll->other_ans){
      if(count($variant)>0){?>
        <div class="__other_ans_text hidden">
            <?=$form->field($model, 'txt')->textarea(['row'=>6])->label(false);?>
        </div>
      <?php }else{ ?>
        <input type="hidden" name="<?=$model->formName();?>[ans]" value="-1">
        <?=$form->field($model, 'txt')->textarea(['row'=>6])->label(false);?>
      <?php }
    }
  ?>
  </div>
 <div class="modal-footer">
  <?php if(!empty($btn)){
    echo $btn;
  };?>
 </div>
  <?php ActiveForm::end(); ?>

</div>
