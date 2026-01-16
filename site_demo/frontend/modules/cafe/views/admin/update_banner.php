<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$isAjax = empty($isAjax) ? false : $isAjax;
/* @var $this yii\web\View */
/* @var $model frontend\modules\cafe\models\Cafe */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="cafe-form">

  <?php $form = ActiveForm::begin(); ?>

  <?php
  $lg_list = Yii::$app->cafe->languageList;
  ?>

  <?php if (count($lg_list) > 1) { ?>
    <ul class="nav nav-tabs">
      <?php foreach ($lg_list as $code => $lg) {
        ; ?>
        <li <?= $code == Yii::$app->language ? 'class="active"' : ''; ?>>
          <a data-toggle="tab" href="#tab_<?= $code; ?>">
            <img src="/img/flag/<?= $code; ?>.svg" alt="">
            <?= $lg; ?>
          </a>
        </li>
      <?php }; ?>
    </ul>

    <div class="tab-content">
      <?php foreach ($lg_list as $code => $lg) {
        ; ?>
        <div id="tab_<?= $code; ?>" class="tab-pane fade <?= $code == Yii::$app->language ? 'in active' : ''; ?>">
          <?= $form->field($model, 'selfmode_banner[' . $code . ']')->widget(\mihaildev\ckeditor\CKEditor::className(), [
              'editorOptions' => [
                  'preset' => 'standard', //разработанны стандартные настройки basic, standard, full данную возможность не обязательно использовать
                  'inline' => false, //по умолчанию false
                  'language' => Yii::$app->language,
              ],
          ])->label(false); ?>
        </div>
      <?php }; ?>
    </div>
  <?php } else {
    foreach ($lg_list as $code => $lg) {
      echo $form->field($model, 'selfmode_banner[' . $code . ']')->widget(\mihaildev\ckeditor\CKEditor::className(), [
          'editorOptions' => [
              'preset' => 'standard', //разработанны стандартные настройки basic, standard, full данную возможность не обязательно использовать
              'inline' => false, //по умолчанию false
              'language' => Yii::$app->language,
          ],
      ])->label(false);
    }
  }; ?>
  <hr>
  <?php if (!$isAjax) { ?>
    <div class="form-group margin-top-10 text-right">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>
