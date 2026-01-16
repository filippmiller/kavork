<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

/* @var $model \yii\db\ActiveRecord */
$model = new $generator->modelClass();
$safeAttributes = $model->safeAttributes();
if (empty($safeAttributes)) {
    $safeAttributes = $model->attributes();
}

$not_gen=[
  'create',
  'pass',
  'hash',
  'sess'
];
echo "<?php\n";
?>

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\color\ColorInput;
use frontend\modules\users\models\Users;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-form">

    <?= "<?php " ?>$form = ActiveForm::begin(); ?>

<?php foreach ($generator->getColumns() as $attribute=>$type) {
  if(!canEdit($attribute,$not_gen ))continue;
  if (in_array($attribute, $safeAttributes)) {
    $input=getColumnInput($attribute,$type,$generator);
    if($input){
      echo $input;
    }else{
      echo "    <?= ";
      echo $generator->generateActiveField($attribute);
      echo " ?>";
    }
    echo "\n\n";
  }
} ?>
    <?= '<?php if(!$isAjax){';?>?>
      <div class="form-group">
        <?= "<?= " ?>Html::submitButton(<?= $generator->generateString('Save') ?>, ['class' => 'btn btn-success']) ?>
      </div>
    <?= '<?php }';?>?>
    <?= "<?php " ?>ActiveForm::end(); ?>

</div>


<?php
function getColumnInput($name,$type,$generator){
  if(strpos($name,'color')!==false){
    return '<?= $form->field($model, \''.$name.'\')->widget(ColorInput::classname(), [
        \'options\' => [\'placeholder\' => \'Select color ...\'],
        \'showDefaultPalette\' => false,
        \'pluginOptions\' => \Yii::$app->params["colorPluginOptions"],
    ]);?>';
  }
  if(strpos($name,'franchisee_id')!==false) {
    return '
    <?php if(Yii::$app->user->can(\'AllFranchisee\')) {
      echo $form->field($model, \'franchisee_id\')->dropDownList(Yii::$app->params[\'franchisee\']);
    }?>';
  }

  if($name=='lg'){
    return '<?= $form->field($model, \''.$name.'\')->dropDownList(Yii::$app->params[\'lg_list\']) ?>';
  }

  if(strpos($name,'cafe')!==false){
    return '<?= $form->field($model, \''.$name.'\')->dropDownList(\yii\helpers\ArrayHelper::map((array)Users::getCafesList(), \'id\', \'name\')) ?>';
  }

  return false;
}

function canEdit($attribute,$not_gen ){
  foreach ($not_gen as $txt){
    if(strpos($attribute,$txt)!==false) return false;
  }
  return true;
}
/*
  - Installing kartik-v/yii2-widget-typeahead (v1.0.1): Downloading (100%) Выпадающий поиск
  - Installing kartik-v/yii2-widget-touchspin (v1.2.1): Downloading (100%) Изменение цифр кнопки по бокам
  - Installing kartik-v/yii2-widget-timepicker (v1.0.3): Downloading (100%) Время
  - Installing kartik-v/yii2-widget-switchinput (v1.3.1): Downloading (100%) Пекреключатель (чекбокс как кнопка)
  - Installing kartik-v/yii2-widget-spinner (v1.0.0): Downloading (100%) Индикатор загрузки
  - Installing kartik-v/yii2-widget-sidenav (v1.0.0): Downloading (100%) Меню
  - Installing kartik-v/yii2-widget-select2 (v2.1.1): Downloading (100%) селект
  - Installing kartik-v/bootstrap-star-rating (4.0.3): Downloading (100%) оценка звездами
  - Installing kartik-v/yii2-widget-rating (v1.0.3): Downloading (100%) оценка звездами
  - Installing kartik-v/yii2-widget-rangeinput (v1.0.1): Downloading (100%) ползунок
  - Installing kartik-v/yii2-widget-growl (v1.1.1): Downloading (100%) Уведомлялки
  - Installing ./bootstrap-fileinput (v4.4.8): Downloading (100%)
  - Installing kartik-v/yii2-widget-fileinput (v1.0.6): Downloading (100%)
  - Installing kartik-v/dependent-dropdown (v1.4.8): Downloading (100%)
  - Installing kartik-v/yii2-widget-depdrop (v1.0.4): Downloading (100%)
  - Installing kartik-v/yii2-widget-datetimepicker (v1.4.4): Downloading (100%)
  - Installing kartik-v/yii2-widget-datepicker (v1.4.4): Downloading (100%)
  - Installing kartik-v/yii2-widget-colorinput (v1.0.3): Downloading (100%)
  - Installing kartik-v/yii2-widget-alert (v1.1.1): Downloading (100%)
  - Installing kartik-v/yii2-widget-affix (v1.0.0): Downloading (100%)
  - Installing kartik-v/yii2-widget-activeform (v1.4.9): Downloading (100%)
  - Installing kartik-v/yii2-widgets (v3.4.0): Downloading (100%)
 */