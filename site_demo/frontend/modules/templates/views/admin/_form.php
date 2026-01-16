<?php

use frontend\modules\templates\models\Template;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\templates\models\Template */
/* @var $form yii\bootstrap\ActiveForm */

$scopeList = Template::getScopeLabels();

if (!Yii::$app->user->can('root')) {
	unset($scopeList[Template::SCOPE_DEFAULT]);
}

?>
<div class="template-form">

	<?php $form = ActiveForm::begin(); ?>

	<?php
		echo $form->field($model, 'scope_id')->dropDownList($scopeList);

		echo $form->field($model, 'type_id')->dropDownList(Template::getTypeLabels());
	?>


	<?php if (!$isAjax) { ?>
		<div class="form-group">
			<?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
		</div>
	<?php } ?>
	<?php ActiveForm::end(); ?>

</div>


