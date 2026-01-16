<?php
/**
 * Created by PhpStorm.
 * User: max
 * Date: 25.11.18
 * Time: 11:57
 */

use app\helpers\GridHelper;
use kartik\daterange\DateRangePicker;
use kartik\form\ActiveForm;
use kartik\helpers\Html;
use yii\helpers\ArrayHelper;

$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
  <div class="col-lg-2 col-md-3 col-lg-push-10 col-md-push-9 input-prepend text-right">
    <?= Yii::$app->user->can('ReportView')?Html::a(
        '<i class="fa fa-chevron-left"></i> ' .
        Yii::t('app', 'Back to Info report') .
        ' <i class="fa fa-line-chart"></i>',
        ['index'],
        ['class' => 'btn btn-science-blue btn-block']):'';
    ?>
  </div>
  <div class="col-lg-10 col-md-9 col-lg-pull-2 col-md-pull-3">
    <div class="report-form">
      <?php $form = ActiveForm::begin([
          'id' => 'transactions_filter',
          'options' => [
              'class' => 'form-horizontal'
          ],
          'fieldConfig' => [
              'checkboxTemplate' => "<div class=\"checkbox\">\n{beginLabel}\n{input}<span
              class=\"fa fa-check che_2\"></span>\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
          ]
      ]); ?>
      <fieldset>
        <div class="input-prepend">
          <span class="add-on"><i class="fa fa-calendar"></i></span>
          <?= DateRangePicker::widget(ArrayHelper::merge(
              GridHelper::getFilterDateRangeConfig([], [strtotime("-1 month"), time()], false),
              [
                  'name' => 'datetime_range',
                  'pluginOptions' => [
                      'timePicker' => false,
                  ]
              ])); ?>
        </div>
      </fieldset>
      <fieldset>
        <div class="input-prepend">
          <?= Html::dropDownList('type', 1, [
              1 => Yii::t('report', 'All payment type'),
              2 => Yii::t('report', 'Cash'),
              3 => Yii::t('report', 'Card'),
          ], ['class' => 'opa']) ?>
        </div>
      </fieldset>

      <fieldset>
        <div class="input-prepend">
          <?= Html::dropDownList('source', 1, [
              1 => Yii::t('report', 'All payment source'),
              2 => Yii::t('report', 'Visits'),
              3 => Yii::t('report', 'Shop'),
          ], ['class' => 'opa']) ?>
        </div>
      </fieldset>
      <fieldset>
        <div class="form-group text-right">
          <?= Html::submitButton(
              '<i class="fa fa-file-excel-o"></i> ' . Yii::t('app', 'Download .CSV'),
              [
                  'class' => 'btn btn-info',
				  'style' =>'margin-bottom: 4px;margin-left:15px;'
              ]) ?>
        </div>
      </fieldset>
    </div>
    <?php ActiveForm::end(); ?>
  </div>
  <div class="col-md-12">
    <hr class="margin-off-top margin-bottom-10 ">
  </div>
  <div class="col-md-12" id="transactions_result"></div>
