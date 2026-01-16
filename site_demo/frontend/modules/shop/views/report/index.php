<?php

use app\helpers\GridHelper;
use common\components\widget\GridPageSize;
use frontend\components\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use kartik\daterange\DateRangePicker;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\shop\models\ShopCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Shop Categories');
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

$panelButtons = empty($panelButtons) ? '' : $panelButtons;
$afterTable = empty($panelButtons) ? '' : $afterTable;

?>
<?= $this->render('../_partials/menu'); ?>

<!--<div class="shop-report-filters">-->
<?php
$defaultConfig = GridHelper::getFilterDateRangeConfig();
//echo DateRangePicker::widget(array_merge($defaultConfig, [
//$shopreportsearch = DateRangePicker::widget(array_merge($defaultConfig, [
//     'model'     => $searchModel,
//    'attribute' => 'created_at',
//    'autoUpdateOnInit' => true,
// ]));

?>
<!--</div>-->


<div class="shop-report-index">
  <div id="ajaxCrudDatatable">
    <?= $this->render('_search', ['model' => $searchModel]) ?>
    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
      //'filterModel' => $searchModel,
      //'floatHeader'=>true,
      //'floatHeaderOptions'=>['scrollingTop'=>'50'],
        'pjax' => true,
        'pjaxSettings' => [
            'neverTimeout' => true,
            'beforeGrid' => $this->render('_header'),
        ],
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
      //'export' => false,
        'columns' => $columns,
        'filterSelector' => '.shop-report-filters input, .shop-report-filters select',
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
			 <div class="shop-report-filters">
	' . DateRangePicker::widget(array_merge($defaultConfig, [
                'model' => $searchModel,
                'attribute' => 'created_at',
                'autoUpdateOnInit' => true,
            ])) . '</div>' . $panelButtons . '
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
