<?php
namespace developeruz\db_rbac\views\access;

use frontend\helpers\MyHtml as Html;
use Yii;
use yii\widgets\ActiveForm;

$this->title = Yii::t('db_rbac', 'Редактирование роли: ') . ' ' . $role->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('db_rbac', 'Управление ролями'), 'url' => ['role']];
$this->params['breadcrumbs'][] = Yii::t('db_rbac', 'Редактирование');
?>
<div class="news-index">

  <h3><?= Html::encode($this->title) ?></h3>

  <div class="links-form">

    <?php
    if (!empty($error)) {
      ?>
      <div class="error-summary">
        <?php
        echo implode('<br>', $error);
        ?>
      </div>
      <?php
    }
    ?>

    <?php $form = ActiveForm::begin(); ?>
<div class="col-md-r">
<div class="row">
    <div class="col-md-4 form-group">
       <?= Html::label(Yii::t('db_rbac', 'Название роли'),'name',['class' => 'control-label']);?>
     <?= Html::textInput('name', $role->name, ['class' => 'form-control']); ?>
	  <div style="font-size: 12px;"><?= Yii::t('db_rbac', '* только латинские буквы, цифры и _ -'); ?></div>
    </div>

    <div class="col-md-4 form-group">
       <?= Html::label(Yii::t('db_rbac', 'Текстовое описание'),'description',['class' => 'control-label']); ?>
      <?= Html::textInput('description', $role->description , ['class' => 'form-control']); ?>
    </div>
</div>
    <div class="form-group">
	<div class="row">
	<div class="col-md-6">
      <h5><?= Html::label(Yii::t('db_rbac', 'Разрешенные доступы')); ?></h5>
	</div>
	<div class="col-md-6 text-right">
	  <?= Html::submitButton(Yii::t('db_rbac', 'Сохранить'), ['class' => 'btn btn-success ']) ?>
	</div>
	</div>
	  <hr class="hrmin">
      <!--<?= Html::checkboxList('permissions', null, $permissions, ['separator' => '<br>']); ?>-->
	  <?= Html::checkboxList('permissions', $role_permit, $permissions, ['class' => 'column_gap']); ?>
    </div>


 <div class="row">
    <div class="col-md-12 text-right form-group">
	<hr class="hrmin">
      <?= Html::submitButton(Yii::t('db_rbac', 'Сохранить'), ['class' => 'btn btn-success ']) ?>
    </div>
 </div>
    <?php ActiveForm::end(); ?>
  </div>
  </div>
</div>
<style>
a.btn.btn-science-blue.ch_cafe {
    display: none;
}
</style>