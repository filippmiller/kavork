<?php

namespace frontend\modules\franchisee\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * FranchiseePaymentsSearch represents the model behind the search form of `frontend\modules\franchisee\models\FranchiseePayments`.
 */
class FranchiseePaymentsSearch extends FranchiseePayments
{

  public $created_at_from;
  public $created_at_to;

  public function behaviors()
  {
    return [
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "created_at",
            "dateStartAttribute" => "created_at_from",
            "dateEndAttribute" => "created_at_to",
            "dateFormat" => false,
            "dateStartFormat" => false,
            "dateEndFormat" => false,
        ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'franchisee_id', 'status', 'tariff_id'], 'integer'],
        [['code', 'comment', 'created_at'], 'safe'],
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
    $query = FranchiseePayments::find();

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

    if (!Yii::$app->user->can('AllFranchisee')) {
      $this->franchisee_id = Yii::$app->cafe->franchiseeId;
    }

    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'franchisee_id' => $this->franchisee_id,
        'status' => $this->status,
        'tariff_id' => $this->tariff_id,
    ]);

    if(!YII_DEBUG) {
      $query->andFilterWhere(['not', ['status' => FranchiseePayments::STATUS_WAIT]]);
    }

    $query->andFilterWhere(['like', 'code', $this->code])
        ->andFilterWhere(['like', 'comment', $this->comment]);

    //Filter for ranger created_at
    if ($this->created_at_from) {
      $w = [
          'and',
          ['>=', 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_from, null)],
          ['<=', 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_to, null)],
      ];
      $query->andWhere($w);
    }

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
