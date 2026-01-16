<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\users\models\Users */

$this->title = Yii::t('app', 'Update Users: {nameAttribute}', [
    'nameAttribute' => '' . $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax=isset($isAjax)?$isAjax:false;
?>
<div class="users-update">

  <?php if(!$isAjax){?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php }?>

  <?= $this->render('_form', [
    'model' => $model,
    'cafes' =>  $cafes,
    'isAjax' => $isAjax,
  ]) ?>

</div>
