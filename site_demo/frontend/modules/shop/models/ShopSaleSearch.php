<?php

namespace frontend\modules\shop\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ShopSaleSearch represents the model behind the search form of `frontend\modules\shop\models\ShopSale`.
 */
class ShopSaleSearch extends ShopSale
{
  public $updated_at_from;
  public $updated_at_to;
  public $created_at_from;
  public $created_at_to;

  private $slideParams = [
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
        [['id', 'cafe_id', 'visitor_log_id', 'created_by', 'updated_by'], 'integer'],
        [['visitor_id', 'comment', 'data', 'updated_at', 'created_at'], 'safe'],
        [['updated_at', 'created_at'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
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
    $query = ShopSale::find();

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

    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'visitor_log_id' => $this->visitor_log_id,
        'created_by' => $this->created_by,
        'updated_by' => $this->updated_by,
    ]);

    $query->andFilterWhere(['like', 'comment', $this->comment]);

    //Filter for ranger updated_at_from
    if (isset($this->updated_at_from)) {
      $query
          ->andFilterWhere(['>=', 'updated_at', GridHelper::getDbDateFromDateRangeFormat($this->updated_at_from)])
          ->andFilterWhere(['<=', 'updated_at', GridHelper::getDbDateFromDateRangeFormat($this->updated_at_to, 'P1D')]);
    };

    //Filter for ranger created_at_from
    if (isset($this->created_at_from)) {
      $query
          ->andFilterWhere(['>=', 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_from)])
          ->andFilterWhere(['<=', 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_to, 'P1D')]);
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
