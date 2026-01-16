<?php

namespace frontend\modules\cafe\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CafeSearch represents the model behind the search form of `frontend\modules\cafe\models\Cafe`.
 */
class CafeSearch extends Cafe
{
  public $max_person_from;
  public $max_person_to;

  private $slideParams = array(
      'max_person' =>
          array(
              'min' => 0,
              'max' => 200,
              'step' => 1,
          )
  );
  private $slideParamsDb = true;

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'max_person', 'max_person_from', 'max_person_to', 'last_task', 'franchisee_id', 'params_id'], 'integer'],
        [['name', 'address', 'currency'], 'trim'],
        [['name', 'address', 'currency'], 'safe']
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
    $query = Cafe::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    if (!Yii::$app->user->can('AllFranchisee')) {
      $this->franchisee_id = Yii::$app->cafe->franchiseeId;
    }

    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'max_person' => $this->max_person,
        'last_task' => $this->last_task,
        'franchisee_id' => $this->franchisee_id,
        'currency' => $this->currency,
    ]);

    $query->andFilterWhere(['like', '.name', $this->name])
        ->andFilterWhere(['like', '.address', $this->address]);

    if ($this->max_person_from) {
      $query->andFilterWhere(['>=', 'max_person', $this->max_person_from])
          ->andFilterWhere(['<=', 'max_person', $this->max_person_to]);
    }
    return $dataProvider;
  }

  public function getSlideParams($name)
  {
    if ($this->slideParamsDb) {
      $query = Cafe::find();

      $tableName = self::tableName() . '.';

      /*$query->andFilterWhere([
          $tableName . 'franchisee_id' => $this->franchisee_id,
      ]);*/

      $select = [];
      foreach ($this->slideParams as $name => $value) {
        //$select[]='min('.$tableName.$name.') as min_'.$name;
        $select[] = 'max(' . $tableName . $name . ') as max_' . $name;
      }
      $query->select($select);

      $result = $query->asArray()->one();
      foreach ($this->slideParams as $name => &$value) {
        //$value['min']=$result['min_'.$name];
        $value['min'] = 0;
        $value['max'] = $result['max_' . $name];

        $value['min'] = round($value['min'], 2, $value['min'] < 0 ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN);
        $value['max'] = round($value['max'], 2, $value['max'] > 0 ? PHP_ROUND_HALF_UP : PHP_ROUND_HALF_DOWN);
        if ($value['min'] == $value['max']) {
          //$value['min']-=5;
          $value['max'] += 5;
        }
      }
      $this->slideParamsDb = false;
    }

    $base = (isset($this->slideParams[$name]) ? $this->slideParams[$name] : []);
    if (!isset($base['min'])) $base['min'] = isset($base['max']) ? $base['max'] - 100 : 0;
    if (!isset($base['max'])) $base['max'] = $base['min'] + 100;
    if (!isset($base['step'])) $base['step'] = ($base['max'] - $base['min']) / 100;

    return $base;
  }
}
