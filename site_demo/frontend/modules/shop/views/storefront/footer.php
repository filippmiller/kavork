<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 21.09.18
 * Time: 14:49
 */

use frontend\modules\visitor\models\Visitor;
use frontend\modules\visits\models\VisitorLog;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $visitor Visitor */
/* @var $visit VisitorLog */

$cafeCurrency = Yii::$app->cafe->getCurrency();
$visitor = !empty($visitor) ? $visitor : (isset($visit) ? $visit->visitor : null);
$js = <<<JS
$('#__shop_new_product_form__').on('beforeSubmit', function(e) {
    e.preventDefault();
    $('#__shop_default_view__').removeClass('hidden');
  	$('#__shop_new_item_view__').addClass('hidden');
  	var tpl = $('#__shop_new_product_template__').html();
  	
  	var title = $('.__shop_new_product_template__title_input_').val();
  	var price = $('.__shop_new_product_template__price_input_').val();
  	var quantity = $('#fakeitemform-quantity').val();
  	
  	tpl=tpl.replace(new RegExp("{ randId }","gm"),"-"+(new Date().getTime()))
  	tpl=tpl.replace(new RegExp("{ title }","gm"),title)
  	tpl=tpl.replace(new RegExp("{ price }","gm"),price)
  	tpl=tpl.replace(new RegExp("{ quantity }","gm"),quantity)
  	console.log(tpl);
  	
  	var productsList = $('#shop-modal-list-products-form');
  	var tmp = $('<div/>').html(tpl);

  	tmp.find('.__shop_new_product_template__tax_required_').val($('.__shop_new_product_template__tax_required_input_').is(':checked') ? 1 : 0);
    
    productsList.prepend(tmp.html());	
  	shop_recalculate();  
  	return false;
});

JS;
$this->registerJs($js);

?>
<div class="shop-pseudo-footer">
  <label>
    <input type="checkbox" name="show_out_of_stock" class="hidden show_out_of_stock">
    <span class="fa fa-check che_2"></span>
    <?= Yii::t('app', 'Show <b>Out of stok</b>'); ?>
  </label>
  <?= $this->render('_footer_summary', [
      'visitor' => $visitor,
      'sum' => 0,
      'cost' => 0,
      'vat' => [],
      'vatSummary' => 0,
      'quantitySummary' => 0,
  ]); ?>

  <?php if ($visit): ?>
    <div class="row padding-bottom-15">
      <div class="col-xs-6 text-left">
        <?php
        echo Html::a('<i class="icon-metro-arrow-left"></i> '.Yii::t('app', 'Back'),
            ['/visits/view', 'id' => $visit->id],
            ['class' => 'btn btn-default', "role" => "modal-remote"]
        );
        echo '&nbsp;';
        ?>

        <?php
        if (Yii::$app->cafe->can("quickProduct")) {
          echo Html::button('<i class="fa fa-plus"></i> <i class="icon-metro-box"></i> '. Yii::t('app', 'Add quick item') .'', [
              'class' => 'btn btn-science-blue __shop_store_front_add_new_item__',
          ]);
        }

        echo '&nbsp;';
        ?>
      </div>
      <div class="col-xs-6 text-right">
        <?php
        echo Html::button('<i class="fa fa-eraser"></i> '. Yii::t('app', 'Clear') .'', [
            'class' => 'btn btn-warning reset_select_product',
            'type' => 'reset',
        ]);
        ?>

        <?php
        echo Html::button('<i class="fa fa-cart-arrow-down"></i> '. Yii::t('app', 'Add to shopping cart') .'', [
            'class' => 'btn btn-primary',
            'data-url' => Url::to(['/shop/storefront/index', 'visit_id' => $visit->id, 'method' => 'cart']),
            'type' => 'submit',
            'disabled' => 'disabled',
        ]);
        ?>
      </div>
    </div>
  <?php else: ?>
    <?php
    $urlParams = [
        '',
        'method' => 'start',
    ];

    if (!empty($visitor)) {
      $urlParams['visitor_id'] = $visitor->id;
    }
    ?>
    <div class="row padding-bottom-15">
      <!--<div class="col-md-6 without_visit"><?
      //= Yii::t('main', 'Purchase without visiting'); 
      ?></div>-->
      <div class="col-xs-4">
        <?php
        if (Yii::$app->cafe->can("quickProduct")) {
          echo Html::button('<i class="fa fa-plus"></i> <i class="icon-metro-box"></i> ' . Yii::t('app', 'Add quick item'), [
              'class' => 'btn btn-science-blue __shop_store_front_add_new_item__',
          ]);
        }

        echo '&nbsp;';
        ?>
      </div>
      <div class="col-xs-8 text-right">
        <?php

        echo Html::button('<i class="fa fa-eraser"></i> ' . Yii::t('app', 'Clear'), [
            'class' => 'btn btn-warning reset_select_product',
            'type' => 'reset',
        ]);

        echo '&nbsp;';

        $urlParams['method'] = 'pay';

        echo Html::button('<i class="fa fa-money"></i> ' . Yii::t('app', 'Pay Cash'), [
            'class' => 'btn btn-science-blue',
            'data-url' => Url::to(array_merge($urlParams, ['pay_method' => VisitorLog::PAY_METHOD_CASH])),
            'type' => 'submit',
            'disabled' => 'disabled',
        ]);

        echo '&nbsp;';

        echo Html::button('<i class="fa fa-credit-card"></i> ' . Yii::t('app', 'Pay Card'), [
            'class' => 'btn btn-science-blue',
            'data-url' => Url::to(array_merge($urlParams, ['pay_method' => VisitorLog::PAY_METHOD_CARD])),
            'type' => 'submit',
            'disabled' => 'disabled',
        ]);
        ?>

      </div>
    </div>
  <?php endif; ?>

  <?php if ($is_visit): ?>
    <div class="row padding-bottom-15">
      <div class="col-md-12">
        <hr class="hr_fake_foot">
        <div class="row">
          <div class="col-xs-4">
            <?php
            $urlParams['method'] = 'back';
            $urlParams[0] = '/visits/start';
            ?>
            <?= Html::button('<i class="icon-metro-arrow-left"></i> '.Yii::t('app', 'Back'), [
                'class' => 'btn btn-default pull-left not_disabled',
                'type' => "submit",
                'data-url' => Url::to($urlParams),
            ]); ?>
          </div>
          <div class="col-xs-8 text-right">
            <?php
            $urlParams['method'] = 'start';
            $urlParams[0] = '/shop/storefront/index';
            ?>
            <?= Html::button('<i class="fa fa-play"></i> '.Yii::t('app', 'Buy and start'), [
                'class' => 'btn btn-primary not_disabled',
                'type' => "submit",
                'data-url' => Url::to($urlParams),
            ]); ?>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

</div>