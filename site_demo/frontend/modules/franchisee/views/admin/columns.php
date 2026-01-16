<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\modules\franchisee\models\Franchisee */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="franchisee-form">

  <?php $form = ActiveForm::begin(); ?>

  <ul>
    <?php foreach ($columns as $column) {
      ?>
      <li>
        <label>
          <input <?= in_array($column, $sel_column) ? 'checked' : ''; ?> type="checkbox" name="column[]"
                                                                         value="<?= $column; ?>"><span
              class="fa fa-check"></span>
          <span><?= $model->getAttributeLabel($column); ?></span>
        </label>
      </li>
    <?php } ?>
  </ul>

  <?php ActiveForm::end(); ?>

</div>
