<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 13.10.18
 * Time: 23:10
 */

use frontend\modules\cafe\models\Discount;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\cafe\models\Cafe */

$title = Yii::t('app', 'Update Discounts for Cafe: {nameAttribute}', [
    'nameAttribute' => '' . $cafe->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cafes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $cafe->name, 'url' => ['view', 'id' => $cafe->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax = isset($isAjax) ? $isAjax : false;
?>
  <div class="cafe-discounts-update">

    <?php if (!$isAjax) { ?>
      <h1><?= Html::encode($this->title) ?></h1>
    <?php } ?>

    <div class="cafe-discounts-form">

      <?php $form = ActiveForm::begin(); ?>

      <?= $form->field($model, 'child_discount',['template' => '{label}<div class="input-group">{hint}{input}<span class="input-group-addon curr">%</span></div>{error}'])->textInput([
          'class' => "form-control num",
      ]) ?>

      <?php

      $columns = [
          [
              'name' => 'number',
              'title' => Yii::t('app', 'Number'),
              'defaultValue' => 1,
              'enableError' => true,
              'options' => [
                  'type' => 'textInput',
              ],
          ],
          [
              'name' => 'periodic',
              'title' => Yii::t('app', 'Periodic'),
              'type' => MultipleInputColumn::TYPE_CHECKBOX,
              'defaultValue' => true,
              'enableError' => true,
              'options' => [
                  'type' => 'checkbox',
              ],
          ],
          [
              'name' => 'use',
              'title' => Yii::t('app', 'Use for'),
              'type' => MultipleInputColumn::TYPE_DROPDOWN,
              'defaultValue' => 1,
              'enableError' => true,
              'items' => Discount::getPeriodLabels(),
          ],
          [
              'name' => 'value',
              'title' => Yii::t('app', 'Value %'),
              'defaultValue' => 1,
              'enableError' => true,
              'options' => [
                  'type' => 'number',
              ],
          ],
      ];


      if (Yii::$app->user->can('FranchiseeDiscountUpdate')) {
        echo $form->field($model, 'franchisee_discounts')->widget(MultipleInput::className(), [
            'id' => 'franchisee_discounts_editor',
            'max' => 100,
            'min' => 0, // should be at least 2 rows
            'allowEmptyList' => true,
            'enableGuessTitle' => true,
            'cloneButton' => true,
            'addButtonPosition' => MultipleInput::POS_HEADER, // show add button in the header
            'columns' => $columns,
        ])
            ->label();
      }

      echo $form->field($model, 'cafe_discounts')->widget(MultipleInput::className(), [
          'id' => 'cafe_discounts_editor',
          'max' => 100,
          'min' => 0, // should be at least 2 rows
          'allowEmptyList' => true,
          'enableGuessTitle' => true,
          'cloneButton' => true,
          'addButtonPosition' => MultipleInput::POS_HEADER, // show add button in the header
          'columns' => $columns,
      ])
          ->label();
      ?>


      <?php if (!$isAjax) { ?>
        <div class="form-group">
          <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
        </div>
      <?php } ?>
      <?php ActiveForm::end(); ?>

    </div>

  </div>


<?php
$js = <<< JS
  $('#franchisee_discounts_editor,#cafe_discounts_editor').on('afterAddRow', function(e, row) {
    row.find('input[type=checkbox]').after('<span class="fa fa-check"></span>')
  }).find('input[type=checkbox]').after('<span class="fa fa-check"></span>')
JS;
$this->registerJs($js);