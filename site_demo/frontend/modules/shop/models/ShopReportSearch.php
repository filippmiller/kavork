<?php

namespace frontend\modules\shop\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ShopReportSearch represents the model behind the search form of `frontend\modules\shop\models\ShopTransaction`.
 */
class ShopReportSearch extends ShopTransactionSearch
{
  public $quantity_from;
  public $quantity_to;
  public $price_from;
  public $price_to;
  public $sum_from;
  public $sum_to;
  public $cost_from;
  public $cost_to;
  public $created_at_from;
  public $created_at_to;

  public $_product_title;
  public $_product_barcode;

  private $slideParams = [
      'quantity' =>
          [
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ],
      'price' =>
          [
              'min' => 0,
              'max' => 10000,
              'step' => 0.1,
          ],
      'sum' =>
          [
              'min' => 0,
              'max' => 10000,
              'step' => 0.1,
          ],
      'cost' =>
          [
              'min' => 0,
              'max' => 10000,
              'step' => 0.1,
          ],
  ];

  public function behaviors()
  {
    return [
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "created_at",
            "dateStartAttribute" => "created_at_from",
            "dateEndAttribute" => "created_at_to",
            "dateFormat" => false,
            'dateStartFormat' => false,
            'dateEndFormat' => false,
        ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['operation_type_id', 'sale_id', 'quantity', 'quantity_from', 'quantity_to'], 'integer'],
        [['price', 'price_from', 'price_to', 'sum_from', 'sum_to', 'cost_from', 'cost_to'], 'number'],
        [['vat', 'comment', 'created_at'], 'safe'],
        [['created_at'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],

        [['_product_title', '_product_barcode'], 'string', 'max' => 255],
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
    $query = ShopTransaction::find();

    $tableName = self::tableName() . '.';

    $this->cafe_id = Yii::$app->cafe->getId();

    // add conditions that should always apply here

    /*$query->addSelect([
      "*",
      "SUM(
      CASE WHEN {$tableName}operation_type_id IN (:negative_operations)
          THEN {$tableName}quantity * -1
          ELSE {$tableName}quantity END
      ) as _summary_quantity
      ",
      "SUM({$tableName}sum) as _summary_sum",
      "SUM({$tableName}cost) as _summary_cost",
    ]);
    $query->addParams([
      ':negative_operations' => implode(',', self::negativeQuantityOperations()),
    ]);
    */

    $query->addSelect([
        "*",
        "SUM({$tableName}quantity) as _summary_quantity",
        "SUM({$tableName}sum) as _summary_sum",
        "SUM({$tableName}cost) as _summary_cost",
    ]);
    $query->andWhere(['operation_type_id' => self::OPERATION_TYPE_WRITE_OFF]);


    $vat_list = json_decode(Yii::$app->cafe->params['vat_list'], true);
    foreach ($vat_list as $k => $vat) {
      $query->addSelect(["CAST(sum(quantity*JSON_EXTRACT(vat, '$[{$k}].vat')) AS DECIMAL(10,2)) as _summary_vat_" . $vat['name']]);
    }

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

    // grid filtering conditions
    $query->andFilterWhere([
        $tableName . 'operation_type_id' => $this->operation_type_id,
        $tableName . 'cafe_id' => $this->cafe_id,
        $tableName . 'sale_id' => $this->sale_id,
        $tableName . 'quantity' => $this->quantity,
        $tableName . 'price' => $this->price,
    ]);

    $query->andFilterWhere(['like', $tableName . 'comment', $this->comment]);

    if (
        !empty($this->_product_title) ||
        !empty($this->_product_barcode)
    ) {
      $query->joinWith('product');

      $productTableName = '{{%shop_product}}.';

      $query->andFilterWhere(['like', $productTableName . 'title', $this->_product_title]);
      $query->andFilterWhere(['like', $productTableName . 'barcode', $this->_product_barcode]);
    }

    //Filter for ranger quantity_from
    if (is_numeric($this->quantity_from)) {
      $query
          ->andFilterWhere(['>=', $tableName . 'quantity', (float)$this->quantity_from])
          ->andFilterWhere(['<=', $tableName . 'quantity', (float)$this->quantity_to]);
    };

    //Filter for ranger price_from
    if (is_numeric($this->price_from)) {
      $query
          ->andFilterWhere(['>=', $tableName . 'price', (float)$this->price_from])
          ->andFilterWhere(['<=', $tableName . 'price', (float)$this->price_to]);
    };

    if (is_numeric($this->sum_from)) {
      $query
          ->andFilterWhere(['>=', $tableName . 'sum', (float)$this->sum_from])
          ->andFilterWhere(['<=', $tableName . 'sum', (float)$this->sum_to]);
    };
    if (is_numeric($this->cost_from)) {
      $query
          ->andFilterWhere(['>=', $tableName . 'cost', (float)$this->cost_from])
          ->andFilterWhere(['<=', $tableName . 'cost', (float)$this->cost_to]);
    };

    //Filter for ranger created_at_from
    if (isset($this->created_at_from)) {
      $query
          ->andFilterWhere(['>=', $tableName . 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_from)])
          ->andFilterWhere(['<=', $tableName . 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_to)]);
    };


    $sum_list = [
        'sum(quantity) as quantity',
        'sum(cost) as cost',
        'sum(cost-sum) as vat_',
        'sum(sum) as sum',
    ];
    $vat_list = json_decode(Yii::$app->cafe->params['vat_list'], true);
    foreach ($vat_list as $k => $vat) {
      $sum_list[] = "CAST(sum(quantity*JSON_EXTRACT(vat, '$[{$k}].vat')) AS DECIMAL(10,2)) as " . $vat['name'];
    }
    $query_sum = clone $query;
    $query_sum->params = [];
    $query_sum->select($sum_list);
    $this->total = $query_sum->asArray()->one();
    if (!empty($this->total)) {
      foreach ($this->total as $name => &$value) {
        if ($name == "quantity") continue;
        $value = number_format($value, 2, '.', '&nbsp;');
      }

      $this->totalQuantity = $this->total['quantity'];
      $this->total['vat'] = $vat_list;
      foreach ($this->total['vat'] as &$vat) {
        $vat['vat'] = $this->total[$vat['name']];
      }
    }

    $query->groupBy([$tableName . 'product_id', $tableName . 'price']);

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
