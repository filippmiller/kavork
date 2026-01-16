<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */

/* @var $exception Exception */
use frontend\views\site\assets\ErrorAsset;
use yii\helpers\Html;
ErrorAsset::register($this);

$this->title = $name;
?>
<div class="row text-center site-error">
  <div class="col-sm-6 text-right">
    <h1 class="font-weight-800 letter-spacing-min-2"><?= Yii::t('app', 'Oops!'); ?></h1>
    <h4 class="letter-spacing-min-1">
      <?= nl2br(Html::encode($message)) ?>
    </h4>
    <hr class="hrmin">
    <a href="/" class="btn btn-science-blue"><i class="fa fa-angle-double-left"></i> <?= Yii::t('app', 'Нome'); ?></a>
  </div>
  <div class="col-sm-6 robot_align">
    <img src="/img/robot_error.jpg" alt="error" class="error"></h1>
  </div>
</div>

