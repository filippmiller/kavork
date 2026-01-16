<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use frontend\components\GridView;
use johnitvn\ajaxcrud\CrudAsset; 
use johnitvn\ajaxcrud\BulkButtonWidget;
use common\components\widget\GridPageSize;
use \kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\tasks\models\TaskSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Task list');
$this->params['breadcrumbs'][] = $this->title;

//ddd(Yii::$app->params['lang']);

if(empty($afterTable)){
  $afterTable = "";
}

CrudAsset::register($this);
$defaultConfig = [];
$tasksearch = DatePicker::widget(array_merge($defaultConfig, [
    'id'=>'data_task',
    'model'     => $searchModel,
    'attribute' => 'date',
    //'type' => DatePicker::TYPE_INPUT,
    'removeButton' => false,
  //'autoUpdateOnInit' => true,
    'pluginOptions' => [
        'autoclose'=>true,
        'format' => Yii::$app->params['lang']['date_js2']
    ]
]));

$history = Html::a(
        Yii::t('app', 'DoTask list') .
        ' <i class="fa fa-angle-double-right"></i>',
        ['/tasks/history'],
        ['data-pjax' => 0,'class' => 'btn btn-science-blue margin-left-10'])
?>

<div class="task-index">
	 <div id="ajaxCrudDatatable">
        <?=GridView::widget([
            'id'=>'crud-datatable',
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'filterSelector' => '#tasksearch-date',
            //'floatHeader'=>true,
            //'floatHeaderOptions'=>['scrollingTop'=>'50'],
            'pjax'=>true,
            'striped' => true,
            'condensed' => true,
            'responsive' => true,
            //'export' => false,
            'columns' => $columns,
			'toolbar'=> [
                ['content'=>
                    Html::a('<i class="fa fa-refresh"></i> '.Yii::t('app', 'Reset').'', [''],
                    ['data-pjax' => 1, 'class' => 'btn btn-default', 'title' => Yii::t('app', 'Reset Grid')]) .
					
                Html::a('<i class="fa fa-th-list"></i> '.Yii::t('app', 'Columns').'', ['columns'],
                    ['role' => 'modal-remote', 'title' => Yii::t('app', 'Columns visibled'), 'class' => 'btn btn-default']) .

                '{export}',],
             ],
			 'panelBeforeTemplate' => 
			 '<div class="row"><div class="col-sm-4 mass_but"></div><div class="col-sm-4">'
			 . $tasksearch .
			 '</div><div class="col-sm-4"></div></div><hr class="hrmin"><div class="row"><div class="col-sm-4 padding-off-right vertical-align">
		    ' . GridPageSize::widget() . ' {summary}
		     </div>
		     <div class="col-sm-4 mass_but">
		     ' .($canCreate?Html::a('<i class="fa fa-plus"></i> <i class="icon-metro-alarm"></i> '.Yii::t('app', 'Create new Tasks').'', ['create'],
                    ['role'=>'modal-remote','title'=> Yii::t('app', 'Create new Tasks'),'class'=>'btn btn-science-blue']):''). '
		     ' . $panelButtons . ' ' .$history. '
		     </div>
		    <div class="col-sm-4 padding-off-left but_toolbar">
		    {toolbar}
		    </div>
		    </div>',
            'panel' => [
                'type' => 'default',
                'heading' => false, 
                'after'=>$afterTable,
            ]
        ])?>
    </div>
</div>
<?php Modal::begin([
    "id"=>"ajaxCrudModal",
    "footer"=>"",// always need it for jquery plugin
])?>
<?php Modal::end(); ?>
