<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\cafe\models\CafeParams */

$this->title = Yii::t('app', 'Create Cafe Params');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cafe Params'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="cafe-params-create">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
