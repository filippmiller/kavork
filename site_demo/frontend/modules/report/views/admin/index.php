<?php
use app\helpers\GridHelper;
use kartik\daterange\DateRangePicker;
use kartik\form\ActiveForm;
use kartik\helpers\Html;
use kartik\time\TimePicker;
use yii\helpers\ArrayHelper;

$this->title = Yii::t('report', 'Report');
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="row">
   <div class="col-lg-2 col-md-3 col-lg-push-10 col-md-push-9 input-prepend text-right">
    <?= Yii::$app->user->can('TransactionView')&&Yii::$app->cafe->can('TransactionsView')?Html::a(
        '<i class="fa fa-sort-amount-desc"></i> ' .
        Yii::t('report', 'Go to Transactions') .
        ' <i class="fa fa-chevron-right"></i>',
        ['transactions'],
        ['class' => 'btn btn-science-blue btn-block']):'';
    ?>
   </div>
   <div class="col-lg-10 col-md-9 col-lg-pull-2 col-md-pull-3">
    <div class="report-form">
      <?php $form = ActiveForm::begin([
          'id' => 'report_filter',
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
          <span class="add-on"><i class="fa fa-clock-o"></i></span>
          <?= Html::input('text', 'time_range', (Yii::t('report', 'All Day')), ['class' => 'opa', 'readonly' => true]) ?>

          <div class="tooltip biger">
             <div class="radio">
                <input type="radio" value="0" checked="checked" class="radio" name="tt" id="time_all">
                 <label for="time_all"><?= (Yii::t('report', 'All Day')) ?></label>
              </div>
            
			
              <div class="radio">
                <input type="radio" value="1" name="tt" id="time_costum">
                 <label for="time_costum">
				 <?= (Yii::t('report', 'Custom')) ?>
				 </label>
                <div class="val_time">
                  <div class="push-down-margin-tiny text-info font-weight-600"><?= (Yii::t('report', 'Start of time')) ?></div>
                  <?= TimePicker::widget([
                      'name' => 'begin_time',
                      'value'=>date(Yii::$app->params['lang']['time'],strtotime('00:00')),
                      'pluginOptions' => [
                          'showMeridian' => !Yii::$app->params['lang']['time24Hour'],
                          'minuteStep' => 15,

                      ]
                  ]); ?>

                  <div class="push-down-margin-tiny text-info font-weight-600"><?= (Yii::t('report', 'End of time')) ?></div>
                  <?= TimePicker::widget([
                      'name' => 'end_time',
                      'value'=>date(Yii::$app->params['lang']['time'],strtotime('23:45')),
                      'pluginOptions' => [
                          'showMeridian' => !Yii::$app->params['lang']['time24Hour'],
                          'minuteStep' => 15,

                      ]
                  ]); ?>
                </div>
              </div>
          </div>
        </div>
      </fieldset>
      <fieldset>
        <div class="input-prepend">
          <span class="add-on"><i class="fa fa-bar-chart"></i></span>
          <?= Html::dropDownList('type', 1, [
              1 => Yii::t('report', 'Summary'),
              2 => Yii::t('report', 'Daily'),
              3 => Yii::t('report', 'Weekly'),
              4 => Yii::t('report', 'Monthly'),
              5 => Yii::t('report', 'Duration'),
         ],['class' => 'opa'])?>
		</div>
      </fieldset>	  
      <?php ActiveForm::end(); ?>
	  </div>
    </div>
	<div class="col-md-12"><hr class="margin-off-top margin-bottom-10 "></div>
</div>
   <div id="report_result"></div> 
    

