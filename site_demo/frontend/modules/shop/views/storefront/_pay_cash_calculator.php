<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 19.08.18
 * Time: 15:39
 */

use kartik\touchspin\TouchSpin;
use yii\web\JsExpression;

$js = <<<JS
$('#surrender-spinner').on('keyup', function() {
   $('#surrender-spinner').trigger('touchspin.stopspin');
});

$('.__use_banknote__ a').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();    
    var val = $(this).text();
    $('#surrender-spinner').val(val).trigger('touchspin.stopspin');
});
JS;

$this->registerJs($js);

$count_js = <<<JS
function () {
  setTimeout(function(){
  var spin_val = $('#surrender-spinner').val();
  var sum = $cost;
  var amount = (spin_val - sum);
  var amount_box = $('.__surrender_amount__');
  if(amount<0){
    amount_box.addClass('error');
  }else{
    amount_box.removeClass('error');
  }
  amount_box.html(amount.toFixed(2));
  console.log(e)
  },100)
}
JS;


$banknotes = [];
if (!empty($cafe->param->banknote_list)) {
  $banknotes = explode(',', $cafe->param->banknote_list);
  foreach ($banknotes as $banknoteIndex => $banknoteValue) {
    if ($cost > (int)$banknoteValue) {
      unset($banknotes[$banknoteIndex]);
    }
  }
}

?>
<div class="change_calculate">
<h5 class="change_calculate font-weight-700"><?= Yii::t('app', 'Calculator change'); ?></h5>
<div class="font-weight-600"><?= Yii::t('app', 'Recieved money from client'); ?>:</div>
<div class="input-group">
  <?php
  echo TouchSpin::widget([
      'id' => 'surrender-spinner',
      'name' => '_surrender',
      'options' => ['placeholder' => Yii::t('app', 'Enter sum...')],
      'pluginOptions' => [
          'min' => $cost,
          'max' => 1000,
          'step' => 0.25,
          'decimals' => 2,
          'boostat' => 5,
          'maxboostedstep' => 1,
          //'postfix' => $cafe->currency,
          'postfix' => Yii::$app->cafe->currency,
          'buttonup_txt' => '<i class="fa fa-plus"></i>',
          'buttondown_txt' => '<i class="fa fa-minus"></i>',
      ],
      'pluginEvents' => [
          "touchspin.on.stopspin" => new JsExpression($count_js),
          "touchspin.on.min" => new JsExpression($count_js),
          "touchspin.on.max" => new JsExpression($count_js),
      ],
  ]);
  ?>
  <?php if (!empty($banknotes)): ?>
    <div class="input-group-btn dropup">
      <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
        <?= Yii::t('app', 'Banknote'); ?>
        <span class="caret"></span>
      </button>
      <ul class="dropdown-menu dropdown-info pull-right __use_banknote__" role="menu">
        <?php foreach ($banknotes as $banknoteValue): ?>
          <li><a href="#"><?= $banknoteValue; ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</div>
<h5 class="vid_check padding-top-10">
  <b><?= Yii::t('app', 'Surrender'); ?>:</b>
  <span class="__surrender_amount__ font-weight-700 font-size-20"
        error_msg="<?= Yii::t('app', 'There is not enough money to pay.'); ?>"
        currency="<?= Yii::$app->cafe->currency; ?>"
  >0.00</span>
</h5>

<?php /*
        <!-- <div class="text text-warning">
  <?= Yii::t('app', 'Attention! Surrender is given if the field "Enter sum..." is correctly filled in') ?>
</div>-->
 */ ?>
</div> 