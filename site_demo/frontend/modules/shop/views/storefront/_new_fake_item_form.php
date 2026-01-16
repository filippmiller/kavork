<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 01.10.18
 * Time: 16:58
 */

use frontend\modules\shop\models\forms\FakeItemForm;
use frontend\modules\shop\models\ShopProduct;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$currency = Yii::$app->cafe->getCurrency();
$model = new FakeItemForm();
?>
<script id="__shop_new_product_template__" type="text/x-jquery-tmpl">
<?= $this->render('_new_fake_item_template', ['model' => new ShopProduct()]) ?>
</script>
<div class="row">
<div class="col-xs-12"><h5 class="fake_title"><?=Yii::t('app', 'Add quick item')?></h5></div>
  <div class="col-xs-12">
    <?php $form = ActiveForm::begin([
        'id' => '__shop_new_product_form__',
        'action' => '#',
        'enableClientValidation' => true,
        'fieldConfig' => [
            'checkboxTemplate' => "<div class=\"checkbox\">\n{beginLabel}\n{input}<span
              class=\"fa fa-check che_2\"></span>\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
        ]
    ]); ?>

    <?= $form->field($model, 'title')->textInput([
        'class' => 'form-control __shop_new_product_template__title_input_',
    ]); ?>

    <label class="control-label"><?= Yii::t('app', 'Count'); ?></label>
    <?= \kartik\widgets\TouchSpin::widget([
        'model' => $model,
        'attribute' => 'quantity',
        'value' => 1,
        'pluginOptions' => [
            'step' => 1,
            'min' => 1,
            'max' => 1000,
            'buttonup_txt' => '<i class="fa fa-plus"></i>',
            'buttondown_txt' => '<i class="fa fa-minus"></i>',
        ]
    ]);
    ?>


        <?= $form->field($model, 'price', ['template' => '{label}<div class="input-group">{hint}{input}<span class="input-group-addon curr">' . $currency . '</span></div>{error}'])->textInput([
            'class' => 'form-control __shop_new_product_template__price_input_',
            'value' => "",
        ]); ?>


        <?= $form->field($model, 'tax_required')->checkbox([
            'class' => 'form-control __shop_new_product_template__tax_required_input_',
            'checked' => true
        ]); ?>
	<div class="clearfix"></div>
    <div class="modal-footer form-group text-right">
      <hr class="hr_fake_foot">
      <?= Html::button('<i class="icon-metro-arrow-left"></i> '. Yii::t('app', 'Cancel') .'', ['class' => 'btn btn-default pull-left __shop_store_front_back_to_default_view__']) ?>
      <?= Html::submitButton(Yii::t('app', 'Add item'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
  </div>
</div>
