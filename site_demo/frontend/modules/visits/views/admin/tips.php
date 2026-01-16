<?php

use common\components\widget\GridPageSize;
use frontend\components\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;

//use frontend\components\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\visits\models\VisitorLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Tips');
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);
?>
<div class="visitor-tips">
  <div id="ajaxCrudDatatable">
    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columns,
        'filterSelector' => '#datetime_range,.list_table select',
        'resizableColumns' => true,
        'pjax' => true,
        'responsive' => true,
        'striped' => true,
        'condensed' => true,
        'hover' => true,
      //'perfectScrollbar' => true,
      //'perfectScrollbarOptions' => [
      //'scrollTop' => true,
      //'wheelSpeed' => 10,
      //'useBothWheelAxes' => true,
      //'handlers' => ['click-rail', 'drag-thumb', 'keyboard', 'wheel', 'touch'],
      //'wheelPropagation' => true,
      //],
        'toolbar' => [
            ['content' =>  
			    Html::a('<i class="fa fa-refresh"></i> '.Yii::t('app', 'Reset').'', [''],
                    ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')]) .
					
                Html::a('<i class="fa fa-th-list"></i> '.Yii::t('app', 'Columns').'', ['columns-tips'],
                    ['role' => 'modal-remote', 'title' => Yii::t('app', 'Columns visibled'), 'class' => 'btn btn-default']) .

                '{export}',],
        ],
        'panelBeforeTemplate' => '<div class="row">
		<div class="col-sm-4 vertical-align">
		' . GridPageSize::widget() . ' {summary}
		</div>
		<div class="col-sm-4 mass_but">' .
            \kartik\daterange\DateRangePicker::widget(\yii\helpers\ArrayHelper::merge(
                \app\helpers\GridHelper::getFilterDateRangeConfig([], $searchModel->finish_time, false),
                [
                    'id' => 'datetime_range',
                    'name' => 'VisitorLogSearch[finish_time]',
                    'pluginOptions' => [
                        'timePicker' => false,
                    ]
                ])) .
            '</div>
		<div class="col-sm-4 padding-off-left but_toolbar">
		{toolbar}
		</div>
		</div>',

        'panel' => [
            'type' => 'default',
            'heading' => false,
            'after' => $this->render('_total_tip', [
                'total' => $total,
            ]),
        ],

    ]) ?>
  </div>
</div>

<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
