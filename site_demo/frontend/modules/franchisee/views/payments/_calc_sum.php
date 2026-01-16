<div class="row">
<div class="col-sm-6">
<h6 class="font-weight-400"><?=Yii::t('app', 'Payment period')?>:</h6>
</div>
<div class="col-sm-6 text-right">
<?= $form->field($model, 'count', ['enableLabel' => false])->dropDownList([
    1 => Yii::t('app', '{n} month(1)', ['n' => 1]),
    3 => Yii::t('app', '{n} month(3)', ['n' => 3]),
    6 => Yii::t('app', '{n} month(6)', ['n' => 6]),
    12 => Yii::t('app', '{n} month(12)', ['n' => 12]),
]); ?>
</div>
</div>
<div class="row abs_total"><div class="col-sm-12">
  <h5 class="font-weight-600"><?=Yii::t('app', 'Total amount payablee')?>:  <span class="pull-right">$<span id="sum_to_pay"></span></span></h5>
</div>
</div>
<script>
  function calcPrice() {
    var price = $('[name="<?=$model->formName();?>[tariff_id]"]:checked').data('price');
    var count = $('[name="<?=$model->formName();?>[count]"]').val();

    var val = price * count;
    if (val) {
      $('#sum_to_pay').text((price * count).toFixed(2));
    } else {
      $('#sum_to_pay').text(" 0");
    }
  }

  calcPrice();
  $('[name="<?=$model->formName();?>[tariff_id]"],[name="<?=$model->formName();?>[count]"]').on('change', calcPrice)
</script>