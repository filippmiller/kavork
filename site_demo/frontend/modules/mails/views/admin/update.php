<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\mails\models\TemplateMail */

$this->title = Yii::t('app', 'Update Template Mail');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Template Mails'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="template-mail-update">



  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
