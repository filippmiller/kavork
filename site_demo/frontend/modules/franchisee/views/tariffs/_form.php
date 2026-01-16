<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\franchisee\models\FranchiseeTariffs */
/* @var $form yii\bootstrap\ActiveForm */

$rolesTree = \frontend\modules\cafe\models\CafeAuthItem::getTree();
$role_ids = $model->role_ids;
$errors = $model->errors;
?>

<div class="franchisee-tariffs-form">

  <?php $form = ActiveForm::begin(); ?>
  <div class="row">
    <div class="col-sm-6">
      <div class="form-group">
        <ul class="nav nav-tabs">
          <?php foreach (Yii::$app->params['lg_list'] as $code => $name) { ?>
            <li<?= Yii::$app->language == $code ? ' class="active"' : ''; ?>>
              <a data-toggle="tab" href="#lg-<?= $code; ?>"><?= $name; ?></a>
            </li>
          <?php }; ?>
        </ul>

        <div class="tab-content">
          <?php foreach (Yii::$app->params['lg_list'] as $code => $name) { ?>
            <div id="lg-<?= $code; ?>" class="tab-pane fade<?= Yii::$app->language == $code ? ' active in' : ''; ?>">

              <?= $form->field($model, 'name[' . $code . ']')->textInput(['maxlength' => true]) ?>

              <?= $form
                  ->field($model, 'description[' . $code . ']')
                  ->textarea(['maxlength' => true]) ?>
            </div>
          <?php }; ?>
        </div>
      </div>
	  <div class="row">
	  <div class="col-sm-6">
      <?= $form->field($model, 'cafe_count')->textInput(['maxlength' => true]) ?>
     </div>
	  <div class="col-sm-6">
      <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>
      </div>
	  </div>
      <?= $form->field($model, 'label')->dropDownList(\frontend\modules\franchisee\models\FranchiseeTariffs::getLabelsLabels()); ?>

      <?= $form->field($model, 'active')->dropDownList(\frontend\modules\franchisee\models\FranchiseeTariffs::getActiveLabels()); ?>
    </div>
    <div class="col-sm-6">
      <?= Html::hiddenInput('FranchiseeTariffs[role_ids][]', false); ?>


      <div class="form-group field-franchiseetariffs-day_price required <?= !empty($errors['roles']) ? 'has-error' : ''; ?>">

        <label class="control-label" for="franchiseetariffs-day_price"><?= Yii::t('app', 'Rules'); ?></label>
        <?php
        if (!empty($errors['roles'])) {
          foreach ($errors['roles'] as $msg) {
            ?>
            <p class="help-block help-block-error"><?= $msg; ?></p>
            <?php
          }
        }
        ?>
        <?php foreach ($rolesTree as $role): ?>
          <div class="__NESTED_CHECKBOX_PARENT__">
            <div class="checkbox">
              <label>
                <?php
                echo Html::checkbox('FranchiseeTariffs[role_ids][]', in_array($role['name'], $role_ids), [
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
                        echo Html::checkbox('FranchiseeTariffs[role_ids][]', in_array($childRole['name'], $role_ids), [
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
      </div>
    </div>
  </div>
  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>


