<?php

use frontend\modules\shop\models\ShopCategory;
use frontend\modules\shop\models\ShopSupplier;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\shop\models\ShopProduct */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="shop-product-form">
  <?php $form = ActiveForm::begin([
      'fieldConfig' => [
          'checkboxTemplate' => "<div class=\"checkbox\">\n{beginLabel}\n{input}<span
              class=\"fa fa-check che_2\"></span>\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
      ]
  ]); ?>
  <div class="col-lg-4 col-md-5">
    <div class="img_prew_wrap">
      <img for="ShopProduct[image]" style="max-height: 150px;max-width: 148px;" src="<?= $model->getImageUrl(); ?>">
    </div>
    <script>testImgPrew()</script>
    <?= $form->field($model, 'image')->hiddenInput()->label(false); ?>
    <button type="button" class="btn btn-block btn-neutral file_select" for="ShopProduct[image]">
      <i class="fa fa-plus"></i> <i class="fa fa-file-image-o"></i> <?= Yii::t('main', 'Browse'); ?>
    </button>
  </div>


  <div class="col-lg-8 col-md-7">
    <?php //$form->field($model, 'cafe_id')->dropDownList(ShopBaseModel::getExtendedCafeList()); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]); ?>

    <?= $form->field($model, 'barcode')->textInput(['maxlength' => true]); ?>

    <?php if (Yii::$app->cafe->can('merchandiseAll')) { ?>
	<div class="row">
	<div class="col-md-6">
      <?= $form->field($model, 'supplier_id')->dropDownList(['' => Yii::t('app', 'None')] + ShopSupplier::getList()); ?>
    </div>
	<div class="col-md-6">
      <?= $form->field($model, 'category_id')->dropDownList(['' => Yii::t('app', 'None')] + ShopCategory::getList()); ?>
	</div>  
	</div>  
    <?php } ?>

  </div>
  <div class="col-md-12">
    <?= $form->field($model, 'old_quantity')->hiddenInput(['value' => $model->quantity])->label(false); ?>
    <div class="row">
      <div class="col-md-3 ">
        <?= $form->field($model, 'new_quantity')->textInput(['value' => $model->quantity]); ?>
      </div>
	  <div class="col-md-4">
        <?= $form->field($model, 'in_stock')->checkbox(); ?>
      </div>
      <div class="col-md-2">
        <?= $form->field($model, 'weight')->textInput(); ?>
      </div>
      <div class="col-md-3">
        <?= $form->field($model, 'accounting_critical_minimum')->textInput(); ?>
      </div>
    </div>
    <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
	<div class="row">
	<div class="col-md-12">
		 <?= $form->field($model, 'price', ['template' => '{label}<div class="input-group">{hint}{input}<span class="input-group-addon curr">'. Yii::$app->cafe->currency .'</span></div>{error}'])->textInput(); ?>
	</div>
	</div>
    <div class="row">
	  <div class="col-md-3">
        <?= $form->field($model, 'is_active')->checkbox(); ?>
      </div>
	   <div class="col-md-3">
	    <?= $form->field($model, 'tax_required')->checkbox(); ?>
		</div>
      <?php if (Yii::$app->cafe->can('merchandiseAll') && Yii::$app->cafe->can('shopAll')) { ?>
        <div class="col-md-6">
          <?= $form->field($model, 'external_sale_available')->checkbox(); ?>
        </div>
      <?php }; ?>
    </div>
  </div>
</div>
</div>
<?php if (!$isAjax) { ?>
  <div class="form-group">
    <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
  </div>
<?php } ?>
<?php ActiveForm::end(); ?>




