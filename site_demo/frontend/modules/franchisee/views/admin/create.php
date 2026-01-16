<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\modules\franchisee\models\Franchisee */

$this->title = 'Create Franchisee';
$this->params['breadcrumbs'][] = ['label' => 'Franchisees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="franchisee-create">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
