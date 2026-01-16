<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\tarifs\models\Tarifs */

$this->title = Yii::t('app', 'Update Tarifs: {nameAttribute}' , [
    'nameAttribute' => '' . $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tarifs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax=isset($isAjax)?$isAjax:false;
?>
<div class="tarifs-update">

  <?php if(!$isAjax){?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php }?>

  <?= $this->render('_form', [
    'model' => $model,
    'isAjax' => $isAjax,
  ]) ?>

</div>
