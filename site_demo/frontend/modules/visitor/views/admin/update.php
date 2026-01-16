<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\visitor\models\Visitor */

$this->title = Yii::t('app', 'Update Visitor: {f_name} {l_name}', [
    'f_name' => '' . $model->f_name,
    'l_name' => '' . $model->l_name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Visitors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax=isset($isAjax)?$isAjax:false;
?>
<div class="visitor-update">

  <?php if(!$isAjax){?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php }?>

  <?= $this->render('_form', [
    'model' => $model,
    'isAjax' => $isAjax,
  ]) ?>

</div>
