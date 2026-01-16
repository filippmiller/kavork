<?php

namespace frontend\modules\tariffs\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TarifsSearch represents the model behind the search form of `frontend\modules\tariffs\models\Tariffs`.
 */
class TariffsSearch extends Tariffs
{
  public $cafe_id_from;
  public $cafe_id_to;
  public $min_sum_from;
  public $min_sum_to;
  public $max_sum_from;
  public $max_sum_to;
  public $first_hour_from;
  public $first_hour_to;
  public $next_hour_from;
  public $next_hour_to;
  public $start_visit_from;
  public $start_visit_to;

  private $slideParamsDb = true;

  private $slideParams = array(
      'cafe_id' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
      'min_sum' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 0.1,
          ),
      'max_sum' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 0.1,
          ),
      'first_hour' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 0.1,
          ),
      'start_visit' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 0.1,
          ),
  );

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'type_id', 'cafe_id', 'params_id', 'cafe_id_from', 'cafe_id_to'], 'integer'],
        [['min_sum', 'min_sum_from', 'min_sum_to', 'max_sum', 'max_sum_from', 'max_sum_to', 'first_hour', 'first_hour_from', 'first_hour_to', 'start_visit', 'start_visit_from', 'start_visit_to'], 'number'],
        [['active'], 'safe'],
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
    $query = Tariffs::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'sort' => array(
            'defaultOrder' => [
                'id' => SORT_DESC
            ]
        ),
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    $query->andWhere([
        'OR',
        [
            'cafe_id' => Yii::$app->cafe->getId(),
        ],
        [
            'params_id' => Yii::$app->cafe->getParamsId(),
        ],
    ]);

    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'type_id' => $this->type_id,
        'min_sum' => $this->min_sum,
        'max_sum' => $this->max_sum,
        'first_hour' => $this->first_hour,
        'franchisee_id' => Yii::$app->cafe->franchiseeId,
        'start_visit' => $this->start_visit,
    ]);

    $query->andFilterWhere(['like', '.active', $this->active]);

    $query->andFilterWhere(['>=', 'cafe_id', $this->cafe_id_from])
        ->andFilterWhere(['<=', 'cafe_id', $this->cafe_id_to])
        ->andFilterWhere(['>=', 'min_sum', $this->min_sum_from])
        ->andFilterWhere(['<=', 'min_sum', $this->min_sum_to])
        ->andFilterWhere(['>=', 'max_sum', $this->max_sum_from])
        ->andFilterWhere(['<=', 'max_sum', $this->max_sum_to])
        ->andFilterWhere(['>=', 'first_hour', $this->first_hour_from])
        ->andFilterWhere(['<=', 'first_hour', $this->first_hour_to])
        ->andFilterWhere(['>=', 'start_visit', $this->start_visit_from])
        ->andFilterWhere(['<=', 'start_visit', $this->start_visit_to]);

    return $dataProvider;
  }

  public function getSlideParams($name)
  {
    if ($this->slideParamsDb) {
      $query = Tariffs::find();

      $tableName = self::tableName() . '.';

      $query->andFilterWhere([
          $tableName . 'franchisee_id' => $this->franchisee_id
      ]);

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
