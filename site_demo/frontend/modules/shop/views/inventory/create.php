<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\shop\models\ShopCategory */

$this->title = Yii::t('app', 'Create Shop Category');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Shop Categories'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="shop-category-create">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
