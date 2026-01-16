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

  <?= $form->field($model, 'status')->hiddenInput()->label(false) ?>

  <h6><?=$model->task->text;?></h6>

  <?= $form->field($model, 'comment')
      ->textarea(['rows' => 6])
      ->label(Yii::t('main','Please add comment on the task'))
  ?>


  <?php ActiveForm::end(); ?>

</div>


