<?php

use common\components\widget\GridPageSize;
use frontend\components\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\visits\models\VisitorLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);
?>
<div class="visitor-log-index">
  <div id="ajaxCrudDatatable">
    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $columns,
        'resizableColumns' => true,
      //'floatHeader' => true,
      //'floatOverflowContainer' => true,
      //'floatHeaderOptions' => ['scrollingTop' => '50'],
        'pjax' => true,
        'responsive' => true,
        'striped' => true,
        'condensed' => true,
        'hover' => true,

        'toolbar' => [
            ['content' =>  
			    Html::a('<i class="fa fa-refresh"></i> '.Yii::t('app', 'Reset').'', [''],
                    ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')]) .
					
                Html::a('<i class="fa fa-th-list"></i> '.Yii::t('app', 'Columns').'', ['columns'],
                    ['role' => 'modal-remote', 'title' => Yii::t('app', 'Columns visibled'), 'class' => 'btn btn-default']) .

                '{export}',],
        ],
        'panelBeforeTemplate' => '<div class="row">
		<div class="col-sm-4 padding-off-right vertical-align">
		' . GridPageSize::widget() . ' {summary}
		</div>
		<div class="col-sm-4 mass_but">
		 ' . (($canCreate && false) ? Html::a('<i class="glyphicon glyphicon-plus"></i>', ['/visits/start'],
                ['role' => 'modal-remote', 'title' => Yii::t('app', 'Create new Visitor Logs'), 'class' => 'btn btn-default']) : '') . '
		 ' . $panelButtons . '
		</div>
		<div class="col-sm-4 padding-off-left but_toolbar">
		{toolbar}
		</div>
		</div>',

        'panel' => [
            'type' => 'default',
            'heading' => false,
            'after' => $this->render('_total', [
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
