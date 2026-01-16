<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\polls\models\Polls */

$this->title = Yii::t('app', 'Update Polls: {nameAttribute}', [
    'nameAttribute' => '' . $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Polls'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="polls-update">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
