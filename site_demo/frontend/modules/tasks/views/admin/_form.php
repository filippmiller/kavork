<?php

use frontend\modules\tasks\models\Task;
use kartik\time\TimePicker;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\tasks\models\Task */
/* @var $form yii\bootstrap\ActiveForm */
$defaultConfig = [];

$js = <<<JS
$('[name="Task[type]"').on('change', testTaskType);
testTaskType();

JS;
$this->registerJs($js);


?>

<div class="task-form">

  <?php $form = ActiveForm::begin(); ?>
  <?= $form->field($model, 'text')->textarea(['rows' => 4]) ?>
  <?= $form->field($model, 'type')->dropDownList(Task::getTypes()) ?>

  <?= $form->field($model, 'start_date')->widget('\kartik\date\DatePicker', [
      'attribute' => 'start_date',
    //'type' => DatePicker::TYPE_INPUT,
      'removeButton' => false,
    //'autoUpdateOnInit' => true,
      'pluginOptions' => [
          'autoclose' => false,
          'format' => Yii::$app->params['lang']['date_js2']
      ]
  ]) ?>

  <div class="row">
    <div class="col-sm-6">
      <?= $form->field($model, 'start_time')->widget(TimePicker::class, [
          'pluginOptions' => [
              'showMeridian' => !Yii::$app->params['lang']['time24Hour'],
              'showSeconds' => Yii::$app->params['lang']['timeShowSeconds'],
          ],
      ]); ?>
    </div>
    <div class="col-sm-6">
      <?= $form->field($model, 'end_time')->widget(TimePicker::class, [
          'pluginOptions' => [
              'showMeridian' => !Yii::$app->params['lang']['time24Hour'],
              'showSeconds' => Yii::$app->params['lang']['timeShowSeconds'],
          ],
      ]); ?>
    </div>
  </div>

  <?= $form->field($model, 'period')->textInput(['class' => 'form-control control']) ?>

  <?= $form->field($model, 'weekday')->dropDownList(Yii::$app->cafe->weakdays,
      ['class' => 'form-control control']
  ) ?>

  <?= $form->field($model, 'weak_n')->textInput(['class' => 'form-control control']) ?>

  <?= $form->field($model, 'active')->dropDownList(Task::getActive()) ?>

  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>


