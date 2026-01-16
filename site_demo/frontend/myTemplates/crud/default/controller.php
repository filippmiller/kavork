<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
  $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;
use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
  use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
  use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \yii\web\Response;
use yii\helpers\Html;
use common\components\widget\BulkButtonWidget;

/**
* <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
*/
class <?= $controllerClass ?> extends <?= StringHelper::basename($generator->baseControllerClass) . "\n" ?>
{

private $def_sel_column=[
<?php
$count = 0;
foreach ($generator->getColumnNames() as $name) {
  if (strpos($name, 'pass') !== false || strpos($name, 'hash') !== false || strpos($name, 'sess')) continue;
  if ($name == 'id' || $name == 'created_at' || $name == 'updated_at' || strpos($name, 'data') !== 0) {
    echo "          '" . $name . "',\n";
  } else if (++$count < 6) {
    echo "          '" . $name . "',\n";
  } else if (strpos($name, 'time') !== 0) {
    echo "          '" . $name . "',\n";
  } else {
    echo "          //'" . $name . "',\n";
  }
}
?>
];
/**
* @inheritdoc
*/
public function behaviors()
{
return [
<?php if (!$generator->disableDelate) { ?>      'verbs' => [
  'class' => VerbFilter::className(),
  'actions' => [
  'delete' => ['post'],
  'bulk-delete' => ['post'],
  ],
  ],<?php } ?>
];
}

/**
* Lists all <?= $modelClass ?> models.
* @return mixed
*/
public function actionIndex()
{
<?php if ($generator->enableRBAC) { ?>
  if (Yii::$app->user->isGuest || !Yii::$app->user->can('<?= $generator->rbacName; ?>View')) {
  throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
  return false;
  }

  <?php if (!empty($generator->searchModelClass)): ?>
    $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
  <?php else: ?>
    $dataProvider = new ActiveDataProvider([
    'query' => <?= $modelClass ?>::find(),
    ]);
  <?php endif; ?>

  $canCreate = Yii::$app->user->can('<?= $generator->rbacName; ?>Create');
  $actions = "";
  $actions.= Yii::$app->user->can('<?= $generator->rbacName; ?>Update')?"{update}":"";
  <?php if (!$generator->disableDelate): ?>        $actions.= Yii::$app->user->can('<?= $generator->rbacName; ?>Delete')?"{delete}":"";
  <?php endif; ?>        $panelButtons='';<?php if (!$generator->disableDelate): ?>
    if( Yii::$app->user->can('<?= $generator->rbacName; ?>Delete')){
    $panelButtons = BulkButtonWidget::widget();
    };<?php endif; ?>
<?php } else { ?>
  $canCreate=true;
  <?php if (!$generator->disableDelate): ?>        $actions = "{update}{delete}";<?php endif; ?>
<?php }; ?>

$columns = include(__DIR__.'<?= $generator->getViewPathFromController('_columns'); ?>');
if(Yii::$app->user->isGuest){
$sel_column=Yii::$app->session->get("columns_<?= $modelClass ?>",false);
}else{
$user=Yii::$app->getUser()->getIdentity();
$sel_column=$user->getActiveColumn("columns_<?= $modelClass ?>");
}
if(!$sel_column){
$sel_column=$this->def_sel_column;
}
foreach($columns as $k=>$column){
$column_name=!is_array($column)?$column:(isset($column['attribute'])?$column['attribute']:false);
if($column_name && !in_array($column_name,$sel_column)){
unset($columns[$k]);
}
}
<?php if (!empty($generator->searchModelClass)): ?>

  return $this->render('index', [
  'searchModel' => $searchModel,
  'dataProvider' => $dataProvider,
  'columns' => $columns,
  'canCreate' => $canCreate,
  'panelButtons' => $panelButtons,
  'title'=><?= $generator->generateString($modelClass . ' list'); ?>,
  <?= ($generator->allCafe) ? "            'forAllCafe'=>true," : ""; ?>
  ]);
<?php else: ?>

  return $this->render('index', [
  'dataProvider' => $dataProvider,
  'columns' => $columns,
  'canCreate' => $canCreate,
  'panelButtons'=>$panelButtons,
  'title'=><?= $generator->generateString($modelClass . ' list'); ?>,
  <?= ($generator->allCafe) ? "\n            'forAllCafe'=>true," : ""; ?>
  ]);
<?php endif; ?>
}


/**
* Config column in <?= $modelClass ?> model.
* <?= implode("\n   * ", $actionParamComments) . "\n" ?>
* @return mixed
* @throws NotFoundHttpException if the model cannot be found
*/
public function actionColumns()
{
<?php if ($generator->enableRBAC) { ?>
  if (Yii::$app->user->isGuest || !Yii::$app->user->can('<?= $generator->rbacName; ?>View')) {
  throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
  return false;
  }
<?php }; ?>
$model = new <?= $modelClass ?>();
$searchModel = new <?= $generator->rbacName; ?>Search();

$request = Yii::$app->request;

if(!$request->isAjax){
throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
return false;
}

Yii::$app->response->format = Response::FORMAT_JSON;

if($request->post('column')){
$col=$request->post('column');

if(Yii::$app->user->isGuest){
Yii::$app->session->set("columns_<?= $modelClass ?>",$col);
}else{
$user=Yii::$app->getUser()->getIdentity();
$user->setActiveColumn("columns_<?= $modelClass ?>",$col);
}

return [
'forceReload'=>'#crud-datatable-pjax',
'content'=>Yii::$app->view->closeModal(),
'forceClose' => 'true',
];
}
$actions="";
$columns = include(__DIR__.'<?= $generator->getViewPathFromController('_columns'); ?>');
if(Yii::$app->user->isGuest){
$sel_column=Yii::$app->session->get("columns_<?= $modelClass ?>",false);
}else{
$user=Yii::$app->getUser()->getIdentity();
$sel_column=$user->getActiveColumn("columns_<?= $modelClass ?>");
}
if(!$sel_column){
$sel_column=$this->def_sel_column;
}
foreach($columns as $k=>$column){
$column_name=!is_array($column)?$column:(isset($column['attribute'])?$column['attribute']:false);
if(!$column_name){
unset($columns[$k]);
}else{
$columns[$k]=$column_name;
}
}

return [
'title'=> <?= $generator->generateString("Change visible columns in $modelClass table"); ?>,
'content'=>$this->renderAjax('columns', [
'sel_column' => $sel_column,
'columns' => $columns,
'model' => $model,
'isAjax' => true
]),
'footer'=> Html::button(<?= $generator->generateString('Close'); ?>,['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
Html::button(<?= $generator->generateString('Save'); ?>,['class'=>'btn btn-primary','type'=>"submit"])

];
}

/**
* Creates a new <?= $modelClass ?> model.
* For ajax request will return json object
* and for non-ajax request if creation is successful, the browser will be redirected to the 'view' page.
* @return mixed
*/
public function actionCreate()
{
<?php if ($generator->enableRBAC) { ?>
  if (Yii::$app->user->isGuest || !Yii::$app->user->can('<?= $generator->rbacName; ?>Create')) {
  throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
  return false;
  }
<?php }; ?>
$request = Yii::$app->request;
$model = new <?= $modelClass ?>();

if(!$request->isAjax){
throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
return false;
}

/*
*   Process for ajax request
*/
Yii::$app->response->format = Response::FORMAT_JSON;
if($request->isGet){
return [
'title'=> <?= $generator->generateString("Create new " . $modelClass); ?>,
'content'=>$this->renderAjax('create', [
'model' => $model,
'isAjax' => true
]),
'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
];
}else if($model->load($request->post()) && $model->save()){
return [
'forceReload'=>'#crud-datatable-pjax',
'title'=> <?= $generator->generateString("Create new " . $modelClass); ?>,
'content'=>'<span class="text-success">Create <?= $modelClass ?> success</span>',
'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
Html::a('Create More',['create'],['class'=>'btn btn-primary','role'=>'modal-remote'])

];
}else{
return [
'title'=> <?= $generator->generateString("Create new " . $modelClass); ?>,
'content'=>$this->renderAjax('create', [
'model' => $model,
'isAjax' => true,
]),
'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])

];
}
}

/**
* Updates an existing <?= $modelClass ?> model.
* For ajax request will return json object
* and for non-ajax request if update is successful, the browser will be redirected to the 'view' page.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return mixed
*/
public function actionUpdate(<?= $actionParams ?>)
{
<?php if ($generator->enableRBAC) { ?>
  if (Yii::$app->user->isGuest || !Yii::$app->user->can('<?= $generator->rbacName; ?>Update')) {
  throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
  return false;
  }
<?php }; ?>

$request = Yii::$app->request;
$model = $this->findModel(<?= $actionParams ?>);

if(!$request->isAjax){
throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
return false;
}
/*
*   Process for ajax request
*/
$title=<?= strtr($generator->generateString('Update ' .
    Inflector::camel2words(StringHelper::basename($generator->modelClass)) .
    ': {nameAttribute}', ['nameAttribute' => '{nameAttribute}']), [
    '{nameAttribute}\'' => '\' . $model->' . $generator->getNameAttribute()
]) ?>;
Yii::$app->response->format = Response::FORMAT_JSON;
if($request->isGet){
return [
'title'=> $title,
'content'=>$this->renderAjax('update', [
'model' => $model,
'isAjax' => true,
]),
'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
];
}else if($model->load($request->post()) && $model->save()){
return [
'forceReload'=>'#crud-datatable-pjax',
'title'=> $title,
'content'=>"
<script>$('.modal-header .close').click()</script>",
'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
Html::a('Edit',['update','<?= substr($actionParams, 1) ?>'=><?= $actionParams ?>],['class'=>'btn btn-primary','role'=>'modal-remote'])
];
}else{
return [
'title'=> $title,
'content'=>$this->renderAjax('update', [
'model' => $model,
'isAjax' => true,
]),
'footer'=> Html::button('Close',['class'=>'btn btn-default pull-left','data-dismiss'=>"modal"]).
Html::button('Save',['class'=>'btn btn-primary','type'=>"submit"])
];
}
}
<?php if (!$generator->disableDelate) { ?>
  /**
  * Delete an existing <?= $modelClass ?> model.
  * For ajax request will return json object
  * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
  * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
  * @return mixed
  */
  public function actionDelete(<?= $actionParams ?>)
  {
  <?php if ($generator->enableRBAC) { ?>
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('<?= $generator->rbacName; ?>Delete')) {
    throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
    return false;
    }
  <?php }; ?>
  $request = Yii::$app->request;
  $this->findModel(<?= $actionParams ?>)->delete();

  if($request->isAjax){
  /*
  *   Process for ajax request
  */
  Yii::$app->response->format = Response::FORMAT_JSON;
  return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
  }else{
  /*
  *   Process for non-ajax request
  */
  return $this->redirect(['index']);
  }
  }

  /**
  * Delete multiple existing <?= $modelClass ?> model.
  * For ajax request will return json object
  * and for non-ajax request if deletion is successful, the browser will be redirected to the 'index' page.
  * <?= implode("\n     * ", $actionParamComments) . "\n" ?>
  * @return mixed
  */
  public function actionBulkDelete()
  {
  <?php if ($generator->enableRBAC) { ?>
    if (Yii::$app->user->isGuest || !Yii::$app->user->can('<?= $generator->rbacName; ?>Delete')) {
    throw new \yii\web\ForbiddenHttpException(<?= $generator->generateString("Page does not exist"); ?>);
    return false;
    }
  <?php }; ?>
  $request = Yii::$app->request;
  $pks = explode(',', $request->post( 'pks' )); // Array or selected records primary keys
  foreach ( $pks as $pk ) {
  $model = $this->findModel($pk);
  $model->delete();
  }

  if($request->isAjax){
  /*
  *   Process for ajax request
  */
  Yii::$app->response->format = Response::FORMAT_JSON;
  return ['forceClose'=>true,'forceReload'=>'#crud-datatable-pjax'];
  }else{
  /*
  *   Process for non-ajax request
  */
  return $this->redirect(['index']);
  }

  }<?php }; ?>

/**
* Finds the <?= $modelClass ?> model based on its primary key value.
* If the model is not found, a 404 HTTP exception will be thrown.
* <?= implode("\n     * ", $actionParamComments) . "\n" ?>
* @return <?= $modelClass ?> the loaded model
* @throws NotFoundHttpException if the model cannot be found
*/
protected function findModel(<?= $actionParams ?>)
{
<?php
if (count($pks) === 1) {
  $condition = '$id';
} else {
  $condition = [];
  foreach ($pks as $pk) {
    $condition[] = "'$pk' => \$$pk";
  }
  $condition = '[' . implode(', ', $condition) . ']';
}
?>
if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
return $model;
} else {
throw new NotFoundHttpException(<?= $generator->generateString("Page does not exist"); ?>);
}
}
}
