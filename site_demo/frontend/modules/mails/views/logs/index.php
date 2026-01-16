<?php

use common\components\widget\GridPageSize;
use johnitvn\ajaxcrud\CrudAsset;
use frontend\components\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\mails\models\MailsLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Mails Logs');
$this->params['breadcrumbs'][] = $this->title;

CrudAsset::register($this);

?>
<div class="mails-log-index">
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
