<?php

use frontend\modules\cafe\models\CafeParams;
use frontend\modules\franchisee\models\Franchisee;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model frontend\modules\cafe\models\Cafe */
/* @var $form yii\bootstrap\ActiveForm */
?>

<div class="cafe-form">

  <?php $form = ActiveForm::begin([
      'fieldConfig' => [
          'checkboxTemplate' => "<div class=\"checkbox\">\n{beginLabel}\n{input}<span
              class=\"fa fa-check che_2\"></span>\n{labelTitle}\n{endLabel}\n{error}\n{hint}\n</div>"
      ]
  ]); ?>
  <div class="row">

    <div class="col-sm-4">
      <?= $form->field($model, 'name')->textInput() ?>
      <label class="control-label" for="cafe-new_logo"><?php echo(Yii::t('app', 'Logo')); ?></label>
      <div class="file-input">
        <div class="btn btn-block btn-neutral">
          <i class="fa fa-file-image-o"></i>
          <?php echo(Yii::t('main', 'Browse')); ?>
        </div>
        <?= $form->field($model, 'new_logo')->fileInput(); ?>
      </div>
      <div class="img_prew_wrap">
        <img for="Cafe[new_logo]" style="width: 140px;" src="<?= $model->getLogo(); ?>">
      </div>
      <script>testImgPrew()</script>
    </div>
    <div class="col-sm-4">
      <?php if (Yii::$app->user->can('CafeChangeParam')) {
        echo $form->field($model, 'params_id')->dropDownList(CafeParams::getList());
        ?>
        <div class="tariff_infoline">
          <?= $this->render('../admin-params/view.php', [
              'model' => $model->getParam()->one(),
          ]) ?>
        </div>
        <?php
      } ?>
    </div>
    <div class="col-sm-4">
      <?php if (Yii::$app->user->can('AllFranchisee')) {
        echo $form->field($model, 'franchisee_id')->dropDownList(Franchisee::getList());
      } ?>
      <?php if (Yii::$app->user->can('AllChange')) {
        echo $form->field($model, 'currency')->dropDownList(Yii::$app->params['currency']);
      } ?>
      <?= $form->field($model, 'max_person')->textInput([
          'class' => "form-control num"
      ]) ?>

    </div>
  </div>
  <div class="row">
    <div class="col-md-12"><h5><?php echo(Yii::t('app', 'Letters and Check Printing')) ?></h5></div>
    <div class="col-sm-4">
      <div class="cafe-vat-accounts-update">
        <?php include 'vat_accounts.php'; ?>
      </div>
    </div>
    <div class="col-sm-4">
      <?= $form->field($model, 'address')->textarea(['rows' => 2]) ?>
      <?= $form->field($model, 'pdf_to_mail')->checkbox() ?>
    </div>
    <div class="col-sm-4">
      <?= $form->field($model, 'width')->dropDownList([
          40 => "40mm",
          82 => "82mm",
          120 => "120mm",
      ]); ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <h5><?php echo(Yii::t('main', 'Report')) ?></h5>
      <?php if (Yii::$app->cafe->can('ReportMail')) {
        echo $this->render('_reports', [
          //'model' => $model->report,
            'model' => $model,
            'isAjax' => $isAjax,
            'form' => $form,
        ]);
      }; ?>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12"><h5><?php echo(Yii::t('main', 'Self Service')); ?></h5></div>
  </div>
  <div class="row">
    <div class="col-sm-4">
      <?= $form->field($model, 'selfservice_timaout')->textInput() ?>
    </div>
    <div class="col-sm-4">
      <?= $form->field($model, 'tips_var')->textInput() ?>
    </div>
    <div class="col-sm-4">
      <?= $form->field($model, 'api_key')->textInput(['readonly' => true]) ?>
    </div>
  </div>


  <?php if (!$isAjax) { ?>
    <div class="form-group">
      <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>
  <?php } ?>
  <?php ActiveForm::end(); ?>

</div>


