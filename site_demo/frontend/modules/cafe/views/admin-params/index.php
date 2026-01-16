<?php

use common\components\widget\GridPageSize;
use frontend\components\GridView;
use johnitvn\ajaxcrud\CrudAsset;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\cafe\models\CafeParamsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'CafeParams list');
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);
if (empty($panelButtons)) {
  $panelButtons = '';
}
?>
<div class="cafe-params-index">
  <div id="ajaxCrudDatatable">
    <?= GridView::widget([
        'id' => 'crud-datatable',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'striped' => true,
        'condensed' => true,
        'responsive' => true,
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
		     ' . ($canCreate ? Html::a('<i class="fa fa-plus"></i><i class="glyphicon glyphicon-map-marker"></i> ' . Yii::t('app', 'Create new Cafe Params') . '', ['create'],
                ['role' => 'modal-remote', 'title' => Yii::t('app', 'Create new Cafe Params'), 'class' => 'btn btn-science-blue']) : '') . '
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
