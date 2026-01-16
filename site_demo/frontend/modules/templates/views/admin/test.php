<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 16.09.18
 * Time: 11:37
 */

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \frontend\modules\templates\models\TemplateTestForm */

$this->title = 'Create Template';
$this->params['breadcrumbs'][] = ['label' => 'Templates', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$isAjax=isset($isAjax)?$isAjax:false;
?>
<div class="template-test">

	<?php if(!$isAjax){?>
		<h1><?= Html::encode($this->title) ?></h1>
	<?php }?>

	<div class="template-test-form">

		<?php $form = ActiveForm::begin([
			'fieldConfig' => [
				'checkboxTemplate' => "<div class=\"checkbox\">\n{beginLabel}\n{input}<span
              class=\"fa fa-check\"></span>\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
			]
		]); ?>

		<?php
		foreach ($model->options as $fieldName => $fieldOptions) {
		  $options = $model->getFieldTemplate($fieldOptions,$fieldName);
      $field = $form->field($model, $fieldName,$options);
			$inputType = ArrayHelper::remove($fieldOptions['inputOptions'], 'type');
			$inputOptions = ArrayHelper::getValue($fieldOptions['inputOptions'], 'options', []);

			switch ($inputType) {
				case 'checkbox':
					$field->checkbox($inputOptions);
					break;
				case 'textarea':
					$field->textarea($inputOptions);
					break;
				case 'dropDown':
					$field->dropDownList($fieldOptions['inputOptions']['items'], $inputOptions);
					break;
				case 'dateRange':
          $field->widget(\kartik\daterange\DateRangePicker::classname(), ArrayHelper::merge(
              \app\helpers\GridHelper::getFilterDateRangeConfig([], false, false),
              [
                  'useWithAddon'=>true,
                  'pluginOptions' => [
                      'timePicker' => false,
                  ]
              ]));
					break;
			}

			echo $field;
		}

		?>


		<?php if (!$isAjax) { ?>
			<div class="form-group">
				<?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
			</div>
		<?php } ?>
		<?php ActiveForm::end(); ?>

	</div>

</div>
