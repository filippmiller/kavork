<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\cafe\models\CafeParams */

$this->title = Yii::t('app', 'Update Cafe Params: {cafe}', [
    'cafe' => '' . $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cafe Params'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="cafe-params-update">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
