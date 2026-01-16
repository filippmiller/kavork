<?php

use yii\helpers\Html;
use Da\QrCode\QrCode;



/* @var $this yii\web\View */
/* @var $model frontend\modules\visitor\models\Visitor */

$this->title = Yii::t('app', 'Card for Visitor: {f_name} {l_name}', [
    'f_name' => '' . $model->f_name,
    'l_name' => '' . $model->l_name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Visitors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Card');
$isAjax=isset($isAjax)?$isAjax:false;
$qrCode = (new QrCode($model->code))
    ->setSize(250)
    ->setMargin(5);
?>
<div class="visitor-card">
  <div class="visitor-card-in " style="margin:0 auto; text-align: center">

  <?php if(!$isAjax){?>
    <h1><?= Html::encode($this->title) ?></h1>
  <?php }?>

    <img src="/img/logo_black.png" alt="">
    <div class="qr-code" style="margin-top: 20px;">
        <img src="<?= $qrCode->writeDataUri() ?>" alt="<?= $model->code ?>">
    </div>
  </div>

</div>
