<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model frontend\modules\mails\models\TemplateMail */

$this->title = Yii::t('app', 'Create Template Mail');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Template Mails'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax = isset($isAjax) ? $isAjax : false;
?>
<div class="template-mail-create">



  <?= $this->render('_form', [
      'model' => $model,
      'isAjax' => $isAjax,
  ]) ?>

</div>
