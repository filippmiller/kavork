<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\franchisee\models\FranchiseeTariffs */

$this->title = Yii::t('app', 'Create Franchisee Tariffs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Franchisee Tariffs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="franchisee-tariffs-create">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
