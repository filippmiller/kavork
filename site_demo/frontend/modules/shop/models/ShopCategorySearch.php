<?php

namespace frontend\modules\shop\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ShopCategorySearch represents the model behind the search form of `frontend\modules\shop\models\ShopCategory`.
 */
class ShopCategorySearch extends ShopCategory
{
  public $updated_at_from;
  public $updated_at_to;
  public $created_at_from;
  public $created_at_to;

  private $slideParams = [
      'updated_at' =>
          [
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ],
      'created_at' =>
          [
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ],
  ];

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'franchisee_id', 'created_by', 'updated_by', 'updated_at', 'updated_at_from', 'updated_at_to', 'created_at', 'created_at_from', 'created_at_to'], 'integer'],
        [['title'], 'safe'],
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
    $query = ShopCategory::find();

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
        'franchisee_id' => $this->franchisee_id,
        'created_by' => $this->created_by,
        'updated_by' => $this->updated_by,
        'updated_at' => $this->updated_at,
        'created_at' => $this->created_at,
    ]);

    $query->andFilterWhere(['like', 'title', $this->title]);

    //Filter for ranger updated_at_from
    if (is_numeric($this->updated_at_from)) {
      $query
          ->andFilterWhere(['>=', 'updated_at', (float)$this->updated_at_from])
          ->andFilterWhere(['<=', 'updated_at', (float)$this->updated_at_to]);
    };

    //Filter for ranger created_at_from
    if (is_numeric($this->created_at_from)) {
      $query
          ->andFilterWhere(['>=', 'created_at', (float)$this->created_at_from])
          ->andFilterWhere(['<=', 'created_at', (float)$this->created_at_to]);
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
