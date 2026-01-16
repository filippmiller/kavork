<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\franchisee\models\FranchiseePayments */

$this->title = Yii::t('app', 'Create Franchisee Payments');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Franchisee Payments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>

<div class="franchisee-payments-create">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'tariffs' => $tariffs,
      'franchisee' => $franchisee,
      'isAjax' => $isAjax,
  ]) ?>

</div>
