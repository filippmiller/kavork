<?php

use frontend\modules\cafe\models\CafeAuthItem;
use kartik\datetime\DateTimePicker;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\franchisee\models\Franchisee */
/* @var $form yii\bootstrap\ActiveForm */
$languageList = Yii::$app->params['lg_list'];
?>

<div class="franchisee-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'active_until')->widget(DateTimePicker::classname(), [
      'options' => ['placeholder' => Yii::t('app', 'Enter Active until date...')],
      'pluginOptions' => [
          'autoclose' => true,
          'format' => 'yyyy-mm-dd hh:ii:ss'
      ]
  ]); ?>

  <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

  <?= $form->field($model, 'max_cafe')->textInput(['maxlength' => true]) ?>

  <?php $rolesTree = CafeAuthItem::getTree();; ?>
  <?= Html::hiddenInput('Franchisee[role_ids][]', false); ?>
  <?php foreach ($rolesTree as $role): ?>
    <div class="__NESTED_CHECKBOX_PARENT__">
      <div class="checkbox">
        <label>
          <?php
          echo Html::checkbox('Franchisee[role_ids][]', in_array($role['name'], $model->roles_ids), [
              'class' => '__main_checkbox__',
              'value' => $role['name'],
          ]);
          ?>
          <span class="fa fa-check"></span>
          <?= $role['description'] ?>
        </label>
        <?php if (!empty($role['children'])): ?>
          <span class="children_control">
                <i class="fa fa-plus"></i>
            </span>
        <?php endif; ?>
        <?php if (!empty($role['children'])): ?>
          <div class="__NESTED_CHECKBOX_CHILDREN__"
               style="padding-left: 25px; padding-bottom: 5px;display:none;">
            <?php foreach ($role['children'] as $childRole): ?>
              <div class="checkbox">
                <label>
                  <?php
                  echo Html::checkbox('Franchisee[role_ids][]', in_array($childRole['name'], $model->roles_ids), [
                      'value' => $childRole['name'],
                  ]);
                  ?>
                  <span class="fa fa-check"></span>
                  <?= $childRole['description'] ?>
                </label>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>


  <?= $form->field($model, 'language_ids')->checkboxList($languageList) ?>

  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>
