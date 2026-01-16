<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\franchisee\models\Franchisee */

$this->title = 'Update Franchisee: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Franchisees', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="franchisee-update">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
