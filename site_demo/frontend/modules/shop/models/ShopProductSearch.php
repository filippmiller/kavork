<?php

namespace frontend\modules\shop\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ShopProductSearch represents the model behind the search form of `frontend\modules\shop\models\ShopProduct`.
 */
class ShopProductSearch extends ShopProduct
{
  public $accounting_critical_minimum_from;
  public $accounting_critical_minimum_to;
  public $weight_from;
  public $weight_to;
  public $price_from;
  public $price_to;

  public $is_shop;
  public $totalQuantity;

  private $slideParamsDb = true;

  private $slideParams = [
      'accounting_critical_minimum' =>
          [
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ],
      'weight' =>
          [
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ],
      'price' =>
          [
              'min' => 0,
              'max' => 100,
              'step' => 0.1,
          ],
  ];

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'supplier_id', 'category_id', 'accounting_critical_minimum', 'accounting_critical_minimum_from', 'accounting_critical_minimum_to', 'weight', 'weight_from', 'weight_to', 'created_by', 'updated_by', 'updated_at', 'created_at'], 'integer'],
        [['title'], 'string', 'max' => 255],
        [['description'], 'string'],
        [['external_sale_available', 'is_active', 'in_stock'], 'integer'],
        [['image', 'barcode'], 'safe'],
        [['price', 'price_from', 'price_to'], 'number'],
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
    $query = ShopProduct::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'sort' => [
            'defaultOrder' => [
                'id' => SORT_DESC,
            ],
        ],
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    $query->andWhere(\Yii::$app->helper->cafe_where($this::tableName()));

    if ($this->is_shop) {
      $this->external_sale_available = 1;
    }

    $query->andFilterWhere(['<', 'is_active', 2]);

    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'in_stock' => $this->in_stock,
        'franchisee_id' => $this->franchisee_id,
        'supplier_id' => $this->supplier_id,
        'category_id' => $this->category_id,
        'accounting_critical_minimum' => $this->accounting_critical_minimum,
        'external_sale_available' => $this->external_sale_available,
        'weight' => $this->weight,
        'price' => $this->price,
        'is_active' => $this->is_active,
        'created_by' => $this->created_by,
        'updated_by' => $this->updated_by,
        'updated_at' => $this->updated_at,
        'created_at' => $this->created_at,
    ]);

    $query->andFilterWhere(['like', 'title', $this->title])
        ->andFilterWhere(['like', 'description', $this->description])
        ->andFilterWhere(['like', 'barcode', $this->barcode]);

    //Filter for ranger accounting_critical_minimum_from
    if (is_numeric($this->accounting_critical_minimum_from)) {
      $query
          ->andFilterWhere(['>=', 'accounting_critical_minimum', (float)$this->accounting_critical_minimum_from])
          ->andFilterWhere(['<=', 'accounting_critical_minimum', (float)$this->accounting_critical_minimum_to]);
    };

    //Filter for ranger weight_from
    if (is_numeric($this->weight_from)) {
      $query
          ->andFilterWhere(['>=', 'weight', (float)$this->weight_from])
          ->andFilterWhere(['<=', 'weight', (float)$this->weight_to]);
    };

    //Filter for ranger price_from
    if (is_numeric($this->price_from)) {
      $query
          ->andFilterWhere(['>=', 'price', (float)$this->price_from])
          ->andFilterWhere(['<=', 'price', (float)$this->price_to]);
    };

    $query->leftJoin(ShopWarehouse::tableName(),
        'id=shop_warehouse.product_id AND ' .
        'shop_warehouse.cafe_id=' . \Yii::$app->cafe->getId()
    );
    $this->totalQuantity =
        (clone $query)
            ->andWhere(['in_stock' => 0])
            ->sum('quantity');
    return $dataProvider;
  }

  public function getSlideParams($name)
  {
    if ($this->slideParamsDb) {
      $query = ShopProduct::find();

      $tableName = self::tableName() . '.';

      $query->andFilterWhere([
          $tableName . 'cafe_id' => $this->cafe_id,
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
