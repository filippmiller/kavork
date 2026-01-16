<?php

namespace frontend\modules\franchisee\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * FranchiseeSearch represents the model behind the search form of `app\modules\franchisee\models\Franchisee`.
 */
class FranchiseeSearch extends Franchisee
{
  public $active_until_from;
  public $active_until_to;
  public $created_at_from;
  public $created_at_to;

  public function behaviors()
  {
    return [
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "active_until",
            "dateStartAttribute" => "active_until_from",
            "dateEndAttribute" => "active_until_to",
            "dateFormat" => false,
            "dateStartFormat" => false,
            "dateEndFormat" => false,
        ],
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
        [['id','tariff_id'], 'integer'],
        [['name', 'active_until', 'code', 'roles', 'created_at'], 'safe'],
        [['active_until', 'created_at'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
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
    $query = Franchisee::find();

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
        'tariff_id' => $this->tariff_id,
    ]);

    $query->andFilterWhere(['like', '.name', $this->name])
        ->andFilterWhere(['like', '.code', $this->code])
        ->andFilterWhere(['like', '.roles', $this->roles]);


    //Filter for ranger active_until_from
    if (isset($this->active_until_from)) {
      $query
          ->andFilterWhere(['>=', 'active_until', GridHelper::getDbDateFromDateRangeFormat($this->active_until_from)])
          ->andFilterWhere(['<=', 'active_until', GridHelper::getDbDateFromDateRangeFormat($this->active_until_to, 'P1D')]);
    };

    //Filter for ranger created_at_from
    if (isset($this->created_at_from)) {
      $query
          ->andFilterWhere(['>=', 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_from)])
          ->andFilterWhere(['<=', 'created_at', GridHelper::getDbDateFromDateRangeFormat($this->created_at_to, 'P1D')]);
    };
    return $dataProvider;
  }
}
