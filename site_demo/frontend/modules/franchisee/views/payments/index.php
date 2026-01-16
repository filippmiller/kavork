<?php
use common\components\widget\GridPageSize;
use frontend\components\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\franchisee\models\FranchiseePaymentsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Personal Account');
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);
?>
<div class="franchisee-payments-index">

  <div class="row">  
	  <div class="col-md-3">
	    <h6 class="modo"><?= Yii::t('app', 'Personal Information') ?></h6>
      <div class="bef_time"><?= Yii::t('app', 'Login') ?>:<span><b><?= $user->name; ?></b></span></div>
      <div class="bef_time"><?= Yii::t('app', 'Email') ?>:<span><b><?= $user->email ? $user->email : '-'; ?></b></span></div>
      <div class="bef_time"><?= Yii::t('app', 'phone') ?>:<span><b><?= $user->phone ? $user->phone : '-'; ?></b></span></div>
	  </div>
      <?php if (!$isRoot) { ?>
	     <div class="col-md-6">
		 <h6 class="modo"><?= Yii::t('app', 'Data Franshise') ?></h6>
		 <div class="row">
		 <div class="col-md-5">
         <div class="bef_time"><?= Yii::t('app', 'Code') ?>: <span><b><?= $franchasee->code; ?></b></span></div>
		 <div class="bef_time"><?= Yii::t('app', 'Created departments') ?>: <span><b><?= $franchasee->cafeCount; ?></b></span></div>
		 <div class="bef_time"><?= Yii::t('app', 'Allowed departments') ?>: <span><b><?= $franchasee->max_cafe; ?></b></span></div>
		 </div>
		 <div class="col-md-7">
		 <div class="bef_time"><?= Yii::t('app', 'Name') ?>: <span><b><?= $franchasee->name; ?></b></span></div>
		 <div class="bef_time"><?= Yii::t('app', 'Creation date') ?>: <span><b>{{local_datetime('<?= $franchasee->created_at; ?>')}}</b></span></div>
         <div class="bef_time"><?= Yii::t('app', 'Blocking date') ?>: <span><b>{{local_datetime('<?= $franchasee->active_until; ?>')}}</b></span></div>
		 </div>
		 </div>
		 </div>
        <?php if (!empty($tariff)) { ?>
		 <div class="col-md-3">
		 <h6 class="modo"><?= Yii::t('app', 'Data Payment') ?></h6>
         <div class="bef_time"><?= Yii::t('app', 'TariffLanding') ?>: <span><b><?= $tariff->lgName; ?></b></span></div>
		 <h5 class="vid_check"> <span class="norm-font"><?= Yii::t('app', 'Balance') ?>:</span> <span class="pull-right"><b>${{_nf(<?= $franchasee->balans; ?>)}}</b></span></h5>
         <!--<div class="vid_check">tariff description <span class="pull-right"><b><?= $tariff->getLgDescription(); ?></b></span></div>-->
         </div>
       <?php } ?>
      <?php } ?>
	
  </div>
  <hr class="hrpay">
  <div class="row">
    <div class="col-sm-6">
      <h5 class="margin-off-bottom"><i class="fa fa-credit-card"></i> <?= Yii::t('app', 'Franchisee Payments') ?></h5>
    </div>
    <div class="col-sm-6 text-right push-up-margin-tiny">
      <?= Yii::$app->user->can('FranchiseeTariffsView') ? Html::a(
          '<i class="fa fa-pencil"></i> ' .
          Yii::t('app', 'Payment Packages') .
          ' <i class="fa fa-angle-double-right"></i>',
          ['/franchisee/tariffs'],
          ['class' => 'btn btn-science-blue']) : '';
      ?>
    </div>
  </div>
  <div id="ajaxCrudDatatable">
    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
      //'floatHeader'=>true,
      //'floatHeaderOptions'=>['scrollingTop'=>'50'],
        'pjax' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
      //'export' => false,
        'columns' => $columns,
        'toolbar' => [
            ['content' =>
                Html::a('<i class="fa fa-refresh"></i> ' . Yii::t('app', 'Reset') . '', [''],
                    ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')]) .

                Html::a('<i class="fa fa-th-list"></i> ' . Yii::t('app', 'Columns') . '', ['columns'],
                    ['role' => 'modal-remote', 'title' => Yii::t('app', 'Columns visibled'), 'class' => 'btn btn-default']) .

                '{export}',],
        ],
        'panelBeforeTemplate' => '<div class="row">
		     <div class="col-sm-4 padding-off-right vertical-align">
		    ' . GridPageSize::widget() . ' {summary}
		     </div>
		     <div class="col-sm-4 mass_but">
		     ' . ($canCreate ? Html::a('<i class="fa fa-plus"></i> <i class="fa fa-credit-card"></i> ' . Yii::t('app', 'Create new Franchisee Payments') . '', ['create'],
                ['role' => 'modal-remote', 'title' => Yii::t('app', 'Create new Franchisee Payments'), 'class' => 'btn btn-science-blue']) : '') . '
		     ' . $panelButtons . '
		     </div>
		    <div class="col-sm-4 padding-off-left but_toolbar">
		    {toolbar}
		    </div>
		    </div>',
        'panel' => [
            'type' => 'default',
            'heading' => false,
            'after' => $afterTable,
        ]
    ]) ?>
  </div>
</div>
<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
