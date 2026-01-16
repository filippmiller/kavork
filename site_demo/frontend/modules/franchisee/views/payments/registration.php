<?php
use frontend\modules\franchisee\assets\FranchiseeAsset;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\modules\franchisee\models\FranchiseeRegistration */
/* @var $form yii\bootstrap\ActiveForm */
$languageList = Yii::$app->params['lg_list'];

FranchiseeAsset::register($this);

?>

<div class="franchisee-form">

  <?php $form = ActiveForm::begin(); ?>
  <div class="row">
    <div class="col-sm-4">
      <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-sm-4">
      <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
    </div>
    <div class="col-sm-4">
      <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12"><h6 class="form-title"><?= Yii::t('app', 'Setting first department') ?></h6></div>
  </div>
  <div class="row">
    <div class="col-sm-6">
      <?= $form->field($model, 'cafe_name')->textInput(['maxlength' => true]) ?>
      <div class="row">
        <div class="col-sm-4">
          <?= $form->field($model, 'currency')->dropDownList(Yii::$app->params['currency']); ?>
        </div>
        <div class=" col-sm-8">
          <?= $form->field($model, 'language_ids')->checkboxList($languageList) ?>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <?= $form->field($model, 'params_id')->dropDownList(\frontend\modules\cafe\models\CafeParams::getList()); ?>
      <div class="tariff_infoline">
        <?= $this->renderAjax('@app/modules/cafe/views/admin-params/view.php', [
            'model' => $model->getParam(),
        ]) ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12"><h6 class="form-title"><?= Yii::t('app', 'Tariff and payment') ?></h6></div>
  </div>
  <div class="row">
    <div class="col-sm-6 TariffLanding" data-mh="paym-group">
	<div class="price-bord-left">
      <h5 class="vid_check font-weight-400 margin-off-top padding-top-10"><?= Yii::t('app', 'TariffLanding') ?>: <span class="pull-right"><?= $model->tariff->lgName; ?></span></h5>
	 <h6 class="vid_check font-weight-400"><?= Yii::t('app', 'DepartmensCount') ?><span class="pull-right"><?= $model->tariff->cafe_count; ?></span></h6>
	  <div class="incl_mod"><?= Yii::t('app', 'includes module groups') ?></div>
      <div class="reg_block_price">
        <?= str_replace("\n", '<br>', $model->tariff->getLgDescription()); ?>
      </div>
    </div>
	</div>
    <div class="col-sm-6 min-height-120 pay_color" data-mh="paym-group">
      <h5 class="vid_check font-weight-400 padding-top-10 margin-off-top"><?= Yii::t('app', 'Price') ?>: <span class="pull-right">$<?= $model->tariff->price; ?></span></h5>
      <input type="radio" name="<?= $model->formName(); ?>[tariff_id]" data-price="<?= $model->tariff->price; ?>"
             checked>
      <?php include '_calc_sum.php'; ?>
    </div>
	</div>
</div>
<?php ActiveForm::end(); ?>

</div>

