<?php

use frontend\modules\visits\models\VisitorLog;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\visits\models\VisitorLog */

$this->title = Yii::t('app', 'Update Visitor Log: {id}', [
    'id' => '' . $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Visitor Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax = isset($isAjax) ? $isAjax : false;

$visit = $model->visit;

?>
<div class="visitor-log-update">

  <?php if(!$isAjax){?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php }?>

  <div class="row">
    <div class="col-sm-6"><b>#</b> <?= $visit->id; ?></div>
    <?php if ($visit->type != VisitorLog::TYPE_ANONYMOUS && $visitor = $visit->visitor): ?>
      <div class="col-sm-6"><b><?= Yii::t('main', 'Phone'); ?>:</b> <?= $visitor->phone; ?></div>
      <div class="col-sm-6"><b><?= Yii::t('main', 'ID'); ?>:</b> <?= $visitor->code; ?></div>
      <div class="col-sm-6"><b><?= Yii::t('main', 'Email'); ?>:</b> <?= $visitor->email; ?></div>
      <div class="col-sm-6"><b><?= Yii::t('main', 'Name'); ?>:</b> <?= $visitor->getFullname(); ?></div>
    <?php endif; ?>
    <div class="col-sm-6"><b><?= Yii::t('main', 'Admin'); ?>:</b> <?= $visit->user->name; ?></div>
  </div>
  <hr class="hrmin">

  <?= $this->render('_form', [
    'model' => $model,
    'isAjax' => $isAjax,
  ]) ?>

</div>
