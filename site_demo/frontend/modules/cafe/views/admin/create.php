<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\cafe\models\Cafe */

$this->title = Yii::t('app', 'Create Cafe');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Cafes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="cafe-create">

  <?php if (!$isAjax) { ?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php } ?>

  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
