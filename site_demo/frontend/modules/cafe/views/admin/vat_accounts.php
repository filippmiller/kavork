<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 20.08.18
 * Time: 23:39
 */

use yii\helpers\Html;

$errors = $model->getErrors();
echo Html::hiddenInput('cafe_id', $model->id);

$vatPossibleNames = $model->getVatNames(true);

foreach ($vatPossibleNames as $vatName) {
  $name = 'vat_code[' . $vatName['name'] . ']';
  ?>
  <div class="form-group field-cafe-vat_code-<?= $vatName['name']; ?>-value">
    <label class="control-label" for="cafe-vat_code-<?= $vatName['name']; ?>-value">
    <!--<?= Yii::t('app', 'Account for') . ' ' . $vatName['name'] . ' (' . $vatName['value'] . '%)'; ?>-->
      <?= Yii::t('app', 'Account for') . ' ' . $vatName['name'] . ' '; ?>
    </label>
    <?= Html::activeInput('text', $model, $name . '[value]', [
        'class' => 'form-control'
    ]); ?>
    <p class="help-block help-block-error"><?= empty($errors[$name]) ? '' : $errors[$name][0] ?></p>
  </div>
<?php } ?>

