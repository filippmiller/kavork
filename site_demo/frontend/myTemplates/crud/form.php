<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $generator yii\gii\generators\crud\Generator */

echo $form->field($generator, 'modelClass');
echo $form->field($generator, 'searchModelClass');
echo $form->field($generator, 'controllerClass');
echo $form->field($generator, 'viewPath');
echo $form->field($generator, 'baseControllerClass');
echo $form->field($generator, 'indexWidgetType')->dropDownList([
    'grid' => 'GridView',
]);
echo $form->field($generator, 'enableI18N')->checkbox();

echo $form->field($generator, 'enablePjax')->checkbox();

echo $form->field($generator, 'enableRBAC')->checkbox();

echo $form->field($generator, 'disableDelate')->checkbox();

echo $form->field($generator, 'messageCategory');


// Autocomplete remaining fields, if modelClass was specified
$script = <<<EOL
(function(\$){\$(document).ready(function(){
  
	\$('#generator-modelclass').on('change', function(){
		var modelClass = $(this).val();
		if ( ! modelClass)
			return;
		
		// get base path
		var paths = modelClass.split('\\\').slice(0, -2);
		var base_path = '';
		for(var path in paths) {
			base_path += base_path ? '\\\' : '';
			base_path += paths[path];
		}
		
		// get model name
		var model_name = modelClass.split('\\\').slice(-1);
		
		model_name="Admin";
		
		controller_path = base_path + '\\\' + 'controllers' + '\\\' + model_name + 'Controller';
		view_path = "@"+base_path + '\\\' + 'views' + '\\\' + model_name.toLowerCase();
		view_path = view_path.split('\\\').join('/');
		
		$('#generator-searchmodelclass').val($(this).val()+'Search');
		$('#generator-controllerclass').val(controller_path);
		$('#generator-viewpath').val(view_path);
		
	});
});})(jQuery);
EOL;
Yii::$app->view->registerJs($script, \yii\web\View::POS_END);
