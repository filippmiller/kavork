<?php

use kartik\time\TimePicker;
use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use frontend\modules\visits\models\VisitorLog;
use frontend\modules\visits\models\VisitorLogEditForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\visits\models\VisitorLog */
/* @var $form yii\bootstrap\ActiveForm */

$present = $model->status == VisitorLogEditForm::STATUS_PRESENT;

$presentOptions = [];
if ($present) {
    $presentOptions['disabled'] = 'disabled';
}

$costOptions = [];
if ($model->recalc_cost) {
    $costOptions['disabled'] = 'disabled';
}

$payList = VisitorLog::payStatusList();
unset($payList[0]);

$js = <<<JS
$('.__toggle_fields__').on('change', function() {
  var el = $(this);
  var form = el.closest('form');
  var val = el.val(); 
   if (val == 1) {
       form.find('.__toggle__').find('select, input').prop('disabled', true);      
       $('.__toggle_cost_field__').prop('checked', true).trigger('change');      
   } else if (val == 2) {       
       form.find('.__toggle__').find('select, input').prop('disabled', false);
       $('.__toggle_cost_field__').prop('checked', true).trigger('change');
   }
});

$('.__toggle_cost_field__').on('change', function() {
  var el = $(this);
  var form = el.closest('form'); 
   if (el.is(':checked')) {
       form.find('.__toggle_cost__').find('select, input').prop('disabled', true);
   } else {
       form.find('.__toggle_cost__').find('select, input').prop('disabled', false);
   }
});
JS;
$this->registerJs($js);

?>
<div class="visitor-log-form">

    <?php $form = ActiveForm::begin([
        'fieldConfig' => [
            'checkboxTemplate' => "<div class=\"checkbox\">\n{beginLabel}\n{input}<span
              class=\"fa fa-check\"></span>\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]
    ]); ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'status')->dropDownList(VisitorLogEditForm::getStatusLabels(), [
                'class' => 'form-control __toggle_fields__'
            ]); ?>
        </div>
        <div class="col-sm-6 __toggle__">
            <?= $form->field($model, 'pay_method')->dropDownList($payList, $presentOptions); ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'add_date')->widget(DatePicker::class, [
                'convertFormat' => true,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'php:' . Yii::$app->params['lang']['date'],
                ],
            ]); ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'add_time')->widget(TimePicker::class, [
                'pluginOptions' => [
                    'showMeridian' => !Yii::$app->params['lang']['time24Hour'],
                    'showSeconds'  => Yii::$app->params['lang']['timeShowSeconds'],
                ],
            ]); ?>
        </div>
        <div class="col-sm-6 __toggle__">
            <?= $form->field($model, 'finish_date')->widget(DatePicker::class, ArrayHelper::merge($presentOptions, [
                'convertFormat' => true,
                'pluginOptions' => [
                    'autoclose' => true,
                    'format'    => 'php:' . Yii::$app->params['lang']['date'],
                ],
            ])); ?>
        </div>
        <div class="col-sm-6 __toggle__">
            <?= $form->field($model, 'finish_time')->widget(TimePicker::class, ArrayHelper::merge($presentOptions, [
                'pluginOptions' => [
                    'showMeridian' => !Yii::$app->params['lang']['time24Hour'],
                    'showSeconds'  => Yii::$app->params['lang']['timeShowSeconds'],
                ],
            ])); ?>
        </div>
        <div class="col-sm-6 __toggle__">
            <br>
            <?= $form->field($model, 'recalc_cost')->checkbox(ArrayHelper::merge($presentOptions, [
                'class' => '__toggle_cost_field__'
            ])); ?>
        </div>
        <div class="col-sm-6 __toggle__ __toggle_cost__">
            <?= $form->field($model, 'cost')->textInput(ArrayHelper::merge($presentOptions, $costOptions)) ?>
        </div>
    </div>

    <?= $form->field($model, 'comment')->textarea(['rows' => 2]) ?>

  <?php if($model->finish_time>0 && $model->pay_method!=0){?>

    <?= Html::a(
        '<i class="fa fa-play"></i> '.Yii::t('app', ' Resume already paid session'),
        ['resume','id'=>$model->id],
        [
            'class' => 'btn bg-blue fg-white',
            'role'  =>  "modal-remote"
        ]
    ) ?>
  <?php };?>

    <?php if(!$isAjax){?>
      <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
      </div>
    <?php }?>
    <?php ActiveForm::end(); ?>

</div>


