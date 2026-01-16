<?php
/**
 * This is the template for generating CRUD search class of the specified model.
 */

use yii\helpers\StringHelper;


/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
  $modelAlias = $modelClass . 'Model';
}
$rules = $generator->generateSearchRules();
$labels = $generator->generateSearchLabels();
$searchAttributes = $generator->getSearchAttributes();
$searchConditions = $generator->generateSearchConditions();
echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->searchModelClass, '\\')) ?>;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use <?= ltrim($generator->modelClass, '\\') . (isset($modelAlias) ? " as $modelAlias" : "") ?>;
use kartik\daterange\DateRangeBehavior;
use yii\db\Expression;
use frontend\modules\visitor\models\Visitor;
use app\helpers\GridHelper;

/**
* <?= $searchModelClass ?> represents the model behind the search form of `<?= $generator->modelClass ?>`.
*/
class <?= $searchModelClass ?> extends <?= isset($modelAlias) ? $modelAlias : $modelClass ?>

{
<?php if ($generator->SearchVarsPublic && count($generator->SearchVarsPublic) > 0) {
  foreach ($generator->SearchVarsPublic as $k => $var) {
    ?>
    public $<?= $k; ?><?php if ($var !== false) { ?>=<?php var_export($var);
    }; ?>;
  <?php } ?>

<?php } ?>
<?php if ($generator->SearchVars && count($generator->SearchVars) > 0) {
  foreach ($generator->SearchVars as $k => $var) {
    ?>
    private $<?= $k; ?><?php if ($var !== false) { ?>=<?php var_export($var);
    }; ?>;
    <?php
  }
}; ?>

<?php if ($generator->behaviors && count($generator->behaviors) > 0) {
  ?>
  public function behaviors()
  {
  return [
  <?php
  foreach ($generator->behaviors as $var) {
    echo "     [\n";
    foreach ($var as $k => $v) {
      echo '       "' . $k . '" => ';
      if ($k == "class") echo $v;
      elseif ($v === false) echo 'false';
      elseif ($v === true) echo 'true';
      else echo '"' . $v . '"';

      echo ",\n";

    }
    echo "      ],\n";
  }
  ?>
  ];
  }
  <?php
}; ?>
/**
* {@inheritdoc}
*/
public function rules()
{
return [
<?= implode(",\n      ", $rules) ?>,
];
}

/**
* {@inheritdoc}
*/
public function scenarios()
{
// bypass scenarios() implementation in the parent class
return Model::scenarios();
}

/**
* Creates data provider instance with search query applied
*
* @param array $params
*
* @return ActiveDataProvider
*/
public function search($params)
{
$query = <?= isset($modelAlias) ? $modelAlias : $modelClass ?>::find();

// add conditions that should always apply here

$dataProvider = new ActiveDataProvider([
'query' => $query,
'sort'=>array(
'defaultOrder'=>[
'id'=>SORT_DESC
]
),
]);

$this->load($params);

if (!$this->validate()) {
// uncomment the following line if you do not want to return any records when validation fails
// $query->where('0=1');
return $dataProvider;
}

<?php if ($generator->has_column("visitor_id")) { ?>
  if($this->visitor_id==Yii::t('app', 'Anonymous')){
  $query->andFilterWhere(['is','visitor_id',(new Expression('Null'))]);
  }else if($this->visitor_id){
  $query->leftJoin('visitor','visitor.id=visitor_id');
  Visitor::findByString($this->visitor_id,$query);
  }


<?php } ?>
<?php if ($generator->has_column("cafe_id")) { ?>
  $user_id=$this->user_id;
  if($user_id==0){
  $user_id=null;
  }else if($this->user_id==-1){
  $query->andFilterWhere(['is','user_id',(new Expression('Null'))]);
  }else if($user_id){
  $query->andFilterWhere(['user_id'=>new Expression('Null')]);
  }


<?php } ?>
// grid filtering conditions
<?= implode("\n        ", $searchConditions) ?>

return $dataProvider;
}
<?php if ($generator->SearchVars && isset($generator->SearchVars['slideParams'])) { ?>

  public function getSlideParams($name){
  $base=(isset($this->slideParams[$name])?$this->slideParams[$name]:[]);
  if(!isset($base['min']))$base['min']=isset($base['max'])?$base['max']-100:0;
  if(!isset($base['max']))$base['max']=$base['min']+100;
  if(!isset($base['step']))$base['step']=($base['max']-$base['min'])/100;

  return $base;
  }
<?php } ?>
}
