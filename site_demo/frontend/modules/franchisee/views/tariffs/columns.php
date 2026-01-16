<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\franchisee\models\FranchiseeTariffs */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="franchisee-tariffs-form">

  <?php $form = ActiveForm::begin(); ?>

  <ul>
    <?php foreach ($columns as $column) {
      ?>
      <li>
        <label>
          <input <?= in_array($column, $sel_column) ? 'checked' : ''; ?> type="checkbox" name="column[]"
                                                                         value="<?= $column; ?>">
          <span class="fa fa-check"></span>
          <span><?= $model->getAttributeLabel($column); ?></span>
        </label>
      </li>
    <?php } ?>
  </ul>

  <?php ActiveForm::end(); ?>

</div>
