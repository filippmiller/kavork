<?php

namespace frontend\modules\franchisee\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * FranchiseeTariffsSearch represents the model behind the search form of `frontend\modules\franchisee\models\FranchiseeTariffs`.
 */
class FranchiseeTariffsSearch extends FranchiseeTariffs
{
  public $day_price_from;
  public $day_price_to;
  public $days_period_from;
  public $days_period_to;
  public $cafe_count_from;
  public $cafe_count_to;

  private $slideParams = array(
      'day_price' =>
          array(
              'min' => 0,
          ),
      'days_period' =>
          array(
              'min' => 0,
              'step' => 1,
          ),
      'cafe_count' =>
          array(
              'min' => 0,
              'step' => 1,
          ),
  );

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'label', 'days_period', 'days_period_from', 'days_period_to', 'active', 'cafe_count_from', 'cafe_count_to'], 'integer'],
        [['cafe_count', 'created_at', 'name'], 'safe'],
        [['day_price', 'day_price_from', 'day_price_to'], 'number'],
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
    $query = FranchiseeTariffs::find();

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

    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'active' => $this->active,
        'label' => $this->label,
    ]);

    $query
        ->andFilterWhere(['like', 'name', $this->name])
        ->andFilterWhere(['like', 'created_at', $this->created_at]);


    //Filter for ranger day_price_from
    if (is_numeric($this->day_price_from)) {
      $query
          ->andFilterWhere(['>=', 'day_price', (float)$this->day_price_from])
          ->andFilterWhere(['<=', 'day_price', (float)$this->day_price_to]);
    };

    //Filter for ranger days_period_from
    if (is_numeric($this->days_period_from)) {
      $query
          ->andFilterWhere(['>=', 'days_period', (float)$this->days_period_from])
          ->andFilterWhere(['<=', 'days_period', (float)$this->days_period_to]);
    };

    //Filter for ranger cafe_count_from
    if (is_numeric($this->cafe_count_from)) {
      $query
          ->andFilterWhere(['>=', 'cafe_count', (float)$this->cafe_count_from])
          ->andFilterWhere(['<=', 'cafe_count', (float)$this->cafe_count_to]);
    };
    return $dataProvider;
  }

  public function getSlideParams($name)
  {
    $base = (isset($this->slideParams[$name]) ? $this->slideParams[$name] : []);
    if (!isset($base['min'])) $base['min'] = isset($base['max']) ? $base['max'] - 100 : 0;
    if (!isset($base['max'])) $base['max'] = $base['min'] + 100;
    if (!isset($base['step'])) $base['step'] = ($base['max'] - $base['min']) / 100;

    return $base;
  }
}
