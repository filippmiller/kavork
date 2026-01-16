<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\shop\models\ShopSupplier */

$this->title = Yii::t('app', 'Create Shop Supplier');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Shop Suppliers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="shop-supplier-create">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
