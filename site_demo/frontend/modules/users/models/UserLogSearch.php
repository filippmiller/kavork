<?php

namespace frontend\modules\users\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * UserLogSearch represents the model behind the search form of `frontend\modules\users\models\UserLog`.
 */
class UserLogSearch extends UserLog
{
  public $start_from;
  public $start_to;
  public $finish_from;
  public $finish_to;


  public function behaviors()
  {
    return [
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "start",
            "dateStartAttribute" => "start_from",
            "dateEndAttribute" => "start_to",
            "dateFormat" => false,
            'dateStartFormat' => false,
            'dateEndFormat' => false,
        ],
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "finish",
            "dateStartAttribute" => "finish_from",
            "dateEndAttribute" => "finish_to",
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
        [['id', 'user_id', 'cafe_id',], 'integer'],
        [['start', 'finish'], 'safe'],
        [['start', 'finish'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
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
    $query = UserLog::find();

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

    $user_id = $this->user_id;
    if ($user_id == 0) {
      $user_id = null;
    } else if ($this->user_id == -1) {
      $query->andFilterWhere(['is', 'user_id', (new Expression('Null'))]);
    } else if ($user_id) {
      $query->andFilterWhere(['user_id' => $user_id]);
    }


    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'cafe_id' => Yii::$app->cafe->id,
    ]);

    //Filter for ranger start_from
    if (isset($this->start_from)) {
      $query
          ->andFilterWhere(['>=', 'start', GridHelper::getDbDateFromDateRangeFormat($this->start_from)])
          ->andFilterWhere(['<=', 'start', GridHelper::getDbDateFromDateRangeFormat($this->start_to, 'P1D')]);
    };

    //Filter for ranger finish_from
    if (isset($this->finish_from)) {
      $query
          ->andFilterWhere(['>=', 'finish', GridHelper::getDbDateFromDateRangeFormat($this->finish_from)])
          ->andFilterWhere(['<=', 'finish', GridHelper::getDbDateFromDateRangeFormat($this->finish_to, 'P1D')]);
    };
    return $dataProvider;
  }

}
