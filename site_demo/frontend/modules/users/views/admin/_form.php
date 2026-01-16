<?php

use frontend\modules\users\models\Users;
use kartik\color\ColorInput;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use frontend\modules\franchisee\models\Franchisee;

/* @var $this yii\web\View */
/* @var $model frontend\modules\users\models\Users */
/* @var $form yii\bootstrap\ActiveForm */
$roleList=Users::getRoleList(false,true);
?>

<div class="users-form">

  <?php $form = ActiveForm::begin(); ?>

<div class="row">
<div class="col-md-6">
        <?= $form->field($model, 'name')->textInput() ?>
</div>
<div class="col-md-6">
        <?= $form->field($model, 'new_password')->textInput() ?>
</div>
<div class="col-md-6">
        <?= $form->field($model, 'roles')->dropDownList($roleList['role'],['options'=>$roleList['allCafe']]) ?>
</div>
<div class="col-md-6">
        <?= $form->field($model, 'lg')->dropDownList(Yii::$app->cafe->languageList) ?>
</div>
<div class="col-md-6">
        <?php if(Yii::$app->user->can('AllFranchisee')) {
          echo $form->field($model, 'franchisee_id')->dropDownList(Franchisee::getList());
        }?>
</div>
<div class="col-md-6">
        <?= $form->field($model, 'email')->textInput() ?>
</div>
<div class="col-md-6">
        <?= $form->field($model, 'phone')->textInput() ?>
</div>
<div class="col-md-6">
        <?= $form->field($model, 'color')->widget(ColorInput::classname(), [
            'options' => ['placeholder' => \Yii::t('app','Select color ...')],
            'showDefaultPalette' => false,
            'pluginOptions' => \Yii::$app->params["colorPluginOptions"],
        ]); ?>
</div>
<div class="col-md-6">
        <?= $form->field($model, 'state')->dropDownList([
            0 => Yii::t('app', 'Active'),
            1 => Yii::t('app', 'Blocked'),
        ]) ?>
</div>
<div class="col-md-12">
  <?php if(isset($cafes) && count($cafes)>0){
    $user_cafes=$model->getCafes()->all();
    $cafe_s=[];
    foreach($user_cafes as $cafe){
      $cafe_s[]=$cafe->cafe_id;
    };
    echo "<div class=\"cafe_list\">";
	echo "<h6>". Yii::t('app', 'Select which departments the user has access to.'). "</h6>";
    foreach($cafes as $k=>$cafe){?>
        <div>
          <label for="cafe_<?=$k;?>">
            <input name="cafe[]" type="checkbox" <?=(in_array($cafe['id'],$cafe_s)?'checked=checked':'');?> value="<?=$cafe['id'];?>" id="cafe_<?=$k;?>">
            <span class="fa fa-check"></span>
            <?=$cafe['name'];?>
          </label>
        </div>

  <?php }
    echo "</div>";
  }?>
  </div>
</div>
  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>



<script>
  $('#users-roles')
    .on('change',function(){
      var otions=$(this).find('option[value='+this.value+']');
      if(typeof(otions.attr('allCafe'))!="undefined" && otions.attr('allCafe')!==false){
        otions.closest('form').find('.cafe_list').hide();
      }else{
        otions.closest('form').find('.cafe_list').show();
      }
    })
    .change();
</script>

