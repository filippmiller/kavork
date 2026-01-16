<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\shop\models\ShopSupplier */

$this->title = Yii::t('app', 'Update Shop Supplier: {title}', [
    'title' => '' . $model->title,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Shop Suppliers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="shop-supplier-update">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
