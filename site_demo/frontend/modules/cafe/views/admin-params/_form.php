<?php

use frontend\modules\cafe\models\CafeParams;
use unclead\multipleinput\MultipleInput;
use unclead\multipleinput\MultipleInputColumn;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\cafe\models\CafeParams */
/* @var $form yii\bootstrap\ActiveForm */
?>

  <div class="cafe-params-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <br>
    <?php
    echo $form->field($model, 'vat_list_items')->widget(MultipleInput::className(), [
        'id' => 'vat_list_items_editor',
        'max' => 6,
        'min' => 0, // should be at least 2 rows
        'allowEmptyList' => true,
        'enableGuessTitle' => true,
        'cloneButton' => true,
        'addButtonPosition' => MultipleInput::POS_HEADER, // show add button in the header
		'addButtonOptions' => [
	    'label' => '<i class="fa fa-plus"></i><span class="text-capitalize"> '. Yii::t('config', 'create') .'</span>',
	    'class' => 'btn btn-default addbutmultiple',
	    ],
        'columns' => [
		
            [
                'name' => 'name',
                'title' => Yii::t('app', 'Name_tax'),
                //'defaultValue' => 'tps',
                'enableError' => true,
                'options' => [
                    'type' => 'text',
					'data-text' => Yii::t('app', 'Name_tax'),
					'placeholder' => Yii::t('app', 'Name_tax'),
                ],
            ],
            [
                'name' => 'value',
                'title' => Yii::t('app', 'Value_vat %'),
                //'defaultValue' => 1,
                'enableError' => true,
                'options' => [
                    'type' => 'text',
					'data-text' => Yii::t('app', 'Value_vat %'),
					'placeholder' => Yii::t('app', 'Value_vat %'),
                ],
            ],
            [
                'name' => 'add_to_cost',
                'title' => Yii::t('app', 'Add to cost'),
                'type' => MultipleInputColumn::TYPE_CHECKBOX,
                'defaultValue' => true,
                'enableError' => true,
                'options' => [
                    'type' => 'checkbox',
					'data-text' => Yii::t('app', 'Add to cost'),
                ],
            ],
            [
                'name' => 'only_for_base_cost',
                'title' => Yii::t('app', 'Only for base cost'),
                'type' => MultipleInputColumn::TYPE_CHECKBOX,
                'defaultValue' => 1,
                'enableError' => true,
                'options' => [
                    'type' => 'checkbox',
                ],
            ],
        ],
    ])
    ->label();
    ?>
    <?php
    if (Yii::$app->user->can('AllChange')) {
      echo $form->field($model, 'banknote_list');
      echo $form->field($model, 'time_zone')->dropDownList(Yii::$app->params['timeZone']);
      echo $form->field($model, 'first_weekday')->dropDownList([
          0 => Yii::t('app', 'Sunday'),
          1 => Yii::t('app', 'Monday'),
          2 => Yii::t('app', 'Tuesday'),
          3 => Yii::t('app', 'Wednesday'),
          4 => Yii::t('app', 'Thursday'),
          5 => Yii::t('app', 'Friday'),
          6 => Yii::t('app', 'Saturday'),
      ]);
      echo $form->field($model, 'time_format')->dropDownList([
          CafeParams::TIME_12 => 12,
          CafeParams::TIME_24 => 24,
      ]);
      echo $form->field($model, 'date_format')->dropDownList([
          CafeParams::DATE_MM_DD_YYYY => Yii::t('app', "MM DD YYYY"),
          CafeParams::DATE_DD_MM_YYYY => Yii::t('app', "DD MM YYYY"),
          CafeParams::DATE_YYYY_MM_DD => Yii::t('app', "YYYY MM DD"),
      ]);
      // echo $form->field($model, 'show_second')->dropDownList([
      //    CafeParams::TIME_SECOND_SHOW=>Yii::t('app',"Show seconds"),
      //    CafeParams::TIME_SECOND_HIDDEN=>Yii::t('app',"Hidden seconds"),
      //]);
    }
    ?>


    <?php if (!$isAjax) { ?>
      <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
      </div>
    <?php } ?>
    <?php ActiveForm::end(); ?>

  </div>


<?php
$js = <<< JS
        $('#vat_list_items_editor').on('afterAddRow', function(e, row) {
            row.find('input[type=checkbox]').after('<span class="fa fa-check"></span>')
        }).find('input[type=checkbox]').after('<span class="fa fa-check"></span>')
JS;
$this->registerJs($js);