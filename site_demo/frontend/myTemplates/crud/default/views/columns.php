<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
  $safeAttributes = $model->attributes();
}

echo "<?php\n";
?>

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

  <?= "<?php " ?>$form = ActiveForm::begin(); ?>

  <ul>
  <?= "<?php " ?>
  foreach ($columns as $column){
    ?>
    <li>
      <label>
        <input <?= "<?=" ?> in_array($column,$sel_column)?'checked':'';?> type="checkbox" name="column[]" value="<?= "<?=" ?>$column;?>">
        <span class="fa fa-check"></span>
        <span><?= "<?=" ?>$model->getAttributeLabel($column);?></span>
      </label>
    </li>
  <?= "<?php " ?>
  } ?>
  </ul>

  <?= "<?php " ?>ActiveForm::end(); ?>

</div>
