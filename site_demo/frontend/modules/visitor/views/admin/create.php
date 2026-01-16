<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\visitor\models\Visitor */

$this->title = Yii::t('app', 'Create Visitor');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Visitors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax=isset($isAjax)?$isAjax:false;
?>
<div class="visitor-create">

  <?php if(!$isAjax){?>
  <h1><?= Html::encode($this->title) ?></h1>
  <?php }?>

  <?= $this->render('_form', [
    'model' => $model,
    'isAjax' => $isAjax,
  ]) ?>

</div>
