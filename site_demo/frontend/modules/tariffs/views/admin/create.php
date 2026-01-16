<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\tarifs\models\Tarifs */

$this->title = Yii::t('app', 'Create Tarifs');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tarifs'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax=isset($isAjax)?$isAjax:false;
?>
<div class="tarifs-create">

  <?php if(!$isAjax){?>
  <h1><?= Html::encode($this->title) ?></h1>
  <?php }?>

  <?= $this->render('_form', [
    'model' => $model,
    'isAjax' => $isAjax,
  ]) ?>

</div>
