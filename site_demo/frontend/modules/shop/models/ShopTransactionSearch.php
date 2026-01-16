<?php

namespace frontend\modules\shop\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ShopTransactionSearch represents the model behind the search form of `frontend\modules\shop\models\ShopTransaction`.
 */
class ShopTransactionSearch extends ShopTransaction
{
  public $quantity_from;
  public $quantity_to;
  public $price_from;
  public $price_to;
  public $sum_from;
  public $sum_to;
  public $cost_from;
  public $cost_to;
  public $updated_at_from;
  public $updated_at_to;
  public $created_at_from;
  public $created_at_to;

  public $_product_title;
  public $_product_barcode;

  public $_visitor_name;

  public $totalQuantity;
  public $total;


  private $query;
  private $slideParamsDb = true;

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
          ],
      'sum' =>
          [
              'min' => 0,
              'max' => 10000,
          ],
      'cost' =>
          [
              'min' => 0,
              'max' => 10000,
          ],
  ];

  public function behaviors()
  {
    return [
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "updated_at",
            "dateStartAttribute" => "updated_at_from",
            "dateEndAttribute" => "updated_at_to",
            "dateFormat" => false,
            'dateStartFormat' => false,
            'dateEndFormat' => false,
        ],
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
        [['id', 'operation_type_id', 'sale_id', 'quantity', 'quantity_from', 'quantity_to', 'created_by', 'updated_by'], 'integer'],
        [['price', 'price_from', 'price_to', 'sum_from', 'sum_to', 'cost_from', 'cost_to'], 'number'],
        [['vat', 'comment', 'updated_at', 'created_at'], 'safe'],
        [['updated_at', 'created_at'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],

        [['_product_title', '_product_barcode', '_visitor_name'], 'string', 'max' => 255],
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

    $query->addSelect(['*', '`cost`-`sum` vat_total']);

    $dataProvider = new ActiveDataProvider([
        'query' => $query,
        'sort' => [
            'defaultOrder' => [
                'id' => SORT_DESC,
            ],
        ],
    ]);

    $dataProvider->sort->attributes = array_merge($dataProvider->sort->attributes, [
        'id' => [
            'asc' => ['shop_transaction.id' => SORT_ASC],
            'desc' => ['shop_transaction.id' => SORT_DESC],
        ]
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    if (is_numeric($this->created_by) && $this->created_by > 0) {
      $query->andFilterWhere([$tableName . 'created_by' => $this->created_by,]);
    }

    $query->leftJoin('{{%shop_product%}}', '{{%shop_product%}}.id=product_id');
    $productTableName = '{{%shop_product}}.';

    // grid filtering conditions
    $query->andFilterWhere([
        $tableName . 'id' => $this->id,
        $tableName . 'operation_type_id' => $this->operation_type_id,
        $tableName . 'cafe_id' => $this->cafe_id,
      //$tableName . 'product_id' => $this->product_id,
        $tableName . 'sale_id' => $this->sale_id,
        $tableName . 'quantity' => $this->quantity,
        $tableName . 'price' => $this->price,
        $tableName . 'updated_by' => $this->updated_by,
    ]);

    $query->andFilterWhere(['like', $tableName . 'comment', $this->comment]);

    if (in_array($this->operation_type_id, [
        ShopTransaction::OPERATION_TYPE_INCOME,
        ShopTransaction::OPERATION_TYPE_WRITE_OFF,
    ])) {
      $query->andFilterWhere([
          $productTableName . 'in_stock' => 0,
      ]);
    }
    if (
        !empty($this->_product_title) ||
        !empty($this->_product_barcode)
    ) {
      //$query->joinWith('product');


      $query->andFilterWhere(['like', $productTableName . 'title', $this->_product_title]);
      $query->andFilterWhere(['like', $productTableName . 'barcode', $this->_product_barcode]);
    }

    if (!empty($this->_visitor_name)) {
      //$query->joinWith('sale.visitor');
      $query->leftJoin('{{%shop_sale%}}', '{{%shop_sale%}}.id=sale_id');
      $query->leftJoin('{{%visitor%}}', '{{%shop_sale%}}.visitor_id={{%visitor%}}.id');

      $visitorTableName = '{{%visitor}}.';

      $conditions = [];

      if ($this->_visitor_name == Yii::t('app', 'Anonymous')) {
        $conditions[] = [$visitorTableName . 'id' => null];
      } elseif (is_numeric($this->_visitor_name)) {
        $conditions[] = [$visitorTableName . 'id' => $this->_visitor_name];
      } else {
        $q = explode(' ', $this->_visitor_name);

        if (count($q) == 1) {
          $conditions[] = ['like', $visitorTableName . 'email', $q[0] . '%', false];
          $conditions[] = ['like', $visitorTableName . 'phone', $q[0]];
          $conditions[] = ['like', $visitorTableName . 'f_name', $q[0] . '%', false];
          $conditions[] = ['like', $visitorTableName . 'l_name', $q[0] . '%', false];
          $conditions[] = ['like', $visitorTableName . 'code', $q[0]];
        } else if (count($q) == 2) {
          $conditions[] = [
              'and',
              ['like', $visitorTableName . 'l_name', $q[0] . '%', false],
              ['like', $visitorTableName . 'f_name', $q[1] . '%', false],
          ];

          $conditions[] = [
              'and',
              ['like', $visitorTableName . 'l_name', $q[1] . '%', false],
              ['like', $visitorTableName . 'f_name', $q[0] . '%', false],
          ];
        }
      }

      $query->andWhere(array_merge(['OR'], $conditions));
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
    //Filter for ranger updated_at_from
    if (isset($this->updated_at_from)) {
      $query
          ->andFilterWhere(['>=', $tableName . 'updated_at', GridHelper::getDbDateFromDateRangeFormat($this->updated_at_from)])
          ->andFilterWhere(['<=', $tableName . 'updated_at', GridHelper::getDbDateFromDateRangeFormat($this->updated_at_to)]);
    };

    //Filter for ranger created_at_from
    if (isset($this->created_at_from)) {
      $query
          ->andFilterWhere(['>=', $tableName . 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_from)])
          ->andFilterWhere(['<=', $tableName . 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_to)]);
    };

    if ($this->is_sale_page) {
      $sum_list = [
          'sum(quantity) as quantity',
          'sum(cost) as cost',
          'sum(cost-sum) as vat_',
          'sum(sum) as sum',
      ];
      $vat_list = json_decode(Yii::$app->cafe->params['vat_list'], true);
      foreach ($vat_list as $k => $vat) {
        $sum_list[] = 'CAST(sum(quantity*JSON_EXTRACT(vat, \'$[' . $k . '].vat\'))  AS DECIMAL(40,2)) as ' . $vat['name'];
      }

      $query_sum = clone $query;
      $query_sum->select($sum_list);
      $this->total = $query_sum->asArray()->one();

      foreach ($this->total as $name => &$value) {
        if ($name == "quantity") continue;
        $value = number_format($value, 2, '.', '&nbsp;');
      }

      $this->totalQuantity = $this->total['quantity'];
      $this->total['vat'] = $vat_list;
      foreach ($this->total['vat'] as &$vat) {
        $vat['vat'] = $this->total[$vat['name']];
      }
    } else {
      $this->totalQuantity = $query->sum('quantity');
    }

    $query->select('shop_transaction.*');

    return $dataProvider;
  }

  public function getSlideParams($param)
  {
    if ($this->slideParamsDb) {
      $query = ShopTransaction::find();

      $tableName = self::tableName() . '.';

      $query->andFilterWhere([
          $tableName . 'operation_type_id' => $this->operation_type_id,
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

    $base = (isset($this->slideParams[$param]) ? $this->slideParams[$param] : []);
    if (!isset($base['min'])) $base['min'] = isset($base['max']) ? $base['max'] - 100 : 0;
    if (!isset($base['max'])) $base['max'] = $base['min'] + 100;
    if (!isset($base['step'])) $base['step'] = ($base['max'] - $base['min']) / 100;

    return $base;
  }
}
