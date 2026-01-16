<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\templates\models\Template */

$this->title = 'Create Template';
$this->params['breadcrumbs'][] = ['label' => 'Templates', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax=isset($isAjax)?$isAjax:false;
?>
<div class="template-create">

  <?php if(!$isAjax){?>
  <h4><?= Html::encode($this->title) ?></h4>
  <?php }?>

  <?= $this->render('_form', [
    'model' => $model,
    'isAjax' => $isAjax,
  ]) ?>

</div>
