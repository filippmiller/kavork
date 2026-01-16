<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\users\models\UserLog */

$this->title = Yii::t('app', 'Update User Log: {id}' , [
    'id' => '' . $model->id,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'User Logs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax=isset($isAjax)?$isAjax:false;
?>
<div class="user-log-update">

  <?php if(!$isAjax){?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php }?>

  <?= $this->render('_form', [
    'model' => $model,
    'isAjax' => $isAjax,
  ]) ?>

</div>
