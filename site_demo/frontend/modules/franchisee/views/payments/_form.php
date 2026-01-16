<?php
use frontend\modules\franchisee\assets\FranchiseeAsset;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

$error = $model->errors;

/* @var $this yii\web\View */
/* @var $model frontend\modules\franchisee\models\FranchiseePayments */
/* @var $form yii\bootstrap\ActiveForm */

$fr_cafe = $franchisee->getCafeCount();

FranchiseeAsset::register($this);
?>

<div class="franchisee-payments-form">

  <?php $form = ActiveForm::begin(); ?>

  <?= $form->field($model, 'tariff_id')->hiddenInput()->label(false); ?>

  <?= Html::hiddenInput('franchisee_id', $model->franchisee_id); ?>

  <div class="row">
    <div class="col-xs-12 hidden-lg hidden-md hidden-sm margin-bottom-20">
      <div class="block_info"><?= Yii::t('app', 'PaymentFranchise_description') ?></div>
    </div>
  </div>
  <div class="row text-center">
    <?php foreach ($tariffs as $tariff) {

      $tariff_dis = ($fr_cafe > $tariff->cafe_count);
      ?>
      <div class="col-sm-4 col-md-4">
        <label class="pricing-pane<?= $tariff_dis ? " disabled" : ''; ?>"
               for="tariff-<?= $tariff->id; ?>">
          <input
              type="radio"
              id="tariff-<?= $tariff->id; ?>"
              name="FranchiseePayments[tariff_id]"
              value= <?= $tariff->id; ?>
              data-price = <?= $tariff->price; ?>
          <?= ($model->tariff_id == $tariff->id && !$tariff_dis) ? "checked" : ''; ?>
          <?= $fr_cafe > $tariff->cafe_count ? "disabled" : ''; ?>
          >

          <h3 class="margin-off-top"><?= $tariff->lgName; ?></h3>
          <hr>
          <p class="price">$<?= $tariff->price; ?></p>
          <div class="month"><?= Yii::t('app', 'month_price') ?></div>
          <hr>
          <div class="text_tariff">
            <h5><?= Yii::t('app', 'DepartmensCount') ?> <?= $tariff->cafe_count; ?></h5>
          </div>
		  <div class="incl_mod"><?= Yii::t('app','includes module groups') ?></div>
          <div class="text_tariff"><?= str_replace("\n", '<br>', $tariff->lgDescription); ?></div>
          <span class="btn btn-science-blue margin-top-10 text-uppercase" <?= $fr_cafe > $tariff->cafe_count ? "disabled" : ''; ?>>
            <?= Yii::t('app', 'Select') ?>
          </span>
        </label>
      </div>

    <?php } ?>
  </div>
    <?php if (!empty($error['tarif_id'])) { ?>
    <div class="has-error">
      <p class="help-block help-block-error">
        <?= $error['tarif_id'][0]; ?>
      </p>
    </div>
  <?php } ?>
  <hr class="margin-top-10">
  <div class="row">
    <div class="col-sm-6 hidden-xs" data-mh="paym-group">
      <div class="block_info"><?= Yii::t('app', 'PaymentFranchise_description') ?></div>
    </div>
    <div class="col-sm-6 pay_color padding-top-10" data-mh="paym-group"><?php include '_calc_sum.php'; ?></div>
  </div>

  <?php ActiveForm::end(); ?>

</div>
