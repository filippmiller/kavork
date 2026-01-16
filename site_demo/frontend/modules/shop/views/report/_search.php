<?php

use yii\bootstrap\ActiveForm;

?>

<div class="post-search">
  <?php $form = ActiveForm::begin([
      'action' => ['index'],
      'method' => 'get',
  ]); ?>


  <?php ActiveForm::end(); ?>
</div>
