<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.09.18
 * Time: 17:53
 */

use frontend\modules\shop\widgets\merchant\models\MerchantIncomeForm;
use frontend\modules\shop\widgets\merchant\models\MerchantSaleForm;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

/* @var $this \yii\web\View */
/* @var $saleModel MerchantSaleForm */
/* @var $incomeModel MerchantIncomeForm */

$js = <<<JS
$('.__shop_merchant form').on('keypress', function(e) {
	if (e.which == 13) {
    	$(this).trigger('submit');
    	return false;
  	} 
  	
  	$(this).yiiActiveForm('resetForm');
});

$('.__shop_merchant form').on('beforeSubmit', function(e) {
	e.preventDefault();
	var yiiform = $(this);
    $.ajax({
	    type: yiiform.attr('method'),
	    url: yiiform.attr('action'),
	    data: yiiform.serializeArray(),
    })
    .done(function(data) {
        if(data.success) {            
            console.log('data is saved');
            yiiform.trigger('reset');
        } else if (data.validation) {
            console.log('server validation failed');            
            yiiform.yiiActiveForm('updateMessages', data.validation, true); // renders validation messages at appropriate places
        } else {
            console.log('incorrect server response');           
        }
        
        if (data.message) {
        	app.notify(data.message.message, data.message.type);
        }
    })
    .fail(function () {
        // request failed
    });

    return false; // prevent default form submission
});
JS;
$this->registerJs($js);

?>

<div class="col-sm-12 __shop_merchant hidden">

	<div class="col-sm-12 __shop_merchant-income-block">
		<?php $incomeForm = ActiveForm::begin([
			'id'             => 'shop-merchant-income-form',
			'action'         => ['shop/merchant/income'],
			'validateOnBlur' => false,
		]); ?>
		<div class="col-sm-7">
			<?php
			echo $incomeForm->field($incomeModel, 'barcode')
				->textInput([
					'placeholder' => Yii::t('main', 'Barcode')
				])
				->label(Yii::t('main', 'Coming in'));
			?>	
		</div>
		<div class="col-sm-3">
			<?= $incomeForm->field($incomeModel, 'quantity'); ?>
		</div>
		<div class="col-sm-2 text-center">
		<span class="text-success" style="font-size: 46px;"><i class="icon-metro-download"></i></span>
		</div>
		<?php ActiveForm::end(); ?>
	</div>

	<div class="col-sm-12 __shop_merchant-sale-block">
		<?php $saleForm = ActiveForm::begin([
			'id'             => 'shop-merchant-sale-form',
			'action'         => ['shop/merchant/sale'],
			'validateOnBlur' => false,
		]); ?>
		<div class="col-sm-7">
			<?php
			echo $saleForm->field($saleModel, 'barcode')
				->textInput([
					'placeholder' => Yii::t('main', 'Barcode')
				])
				->label(Yii::t('main', 'Leaving'));
			?>
		</div>
		<div class="col-sm-3">
			<?= $saleForm->field($saleModel, 'quantity'); ?>
		</div>
		<div class="col-sm-2 text-center">
		<span class="text-warning" style="font-size: 46px;"><i class="icon-metro-upload"></i></span>
		</div>
		<?php ActiveForm::end(); ?>
	</div>

	<?php if (Yii::$app->user->can('ShopInventoryView') && Yii::$app->cafe->can("merchandiseAll")): ?>
	<div class="text-right" style="margin-bottom:10px;">
		<a href="<?= Url::to(['/shop/inventory/index']); ?>" class="btn btn-info">
			<?= Yii::t('main', 'Commodity research'); ?>
		</a>
	</div>
	<?php endif; ?>

</div>
