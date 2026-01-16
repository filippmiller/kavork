<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\shop\models\ShopTransaction */

$this->title = Yii::t('app', 'Create Shop Transaction');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Shop Transactions'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="shop-transaction-create">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
