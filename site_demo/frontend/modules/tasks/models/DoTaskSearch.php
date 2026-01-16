<?php

namespace frontend\modules\tasks\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * DoTaskSearch represents the model behind the search form of `frontend\modules\tasks\models\DoTask`.
 */
class DoTaskSearch extends DoTask
{

  public $datetime_from;
  public $datetime_to;
  public $closedate_from;
  public $closedate_to;

  public function behaviors()
  {
    return [
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "datetime",
            "dateStartAttribute" => "datetime_from",
            "dateEndAttribute" => "datetime_to",
            "dateFormat" => false,
            "dateStartFormat" => false,
            "dateEndFormat" => false,
        ],
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "closedate",
            "dateStartAttribute" => "closedate_from",
            "dateEndAttribute" => "closedate_to",
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
        [['id', 'cafe_id', 'status', 'task_id', 'user_id'], 'integer'],
        [['datetime', 'closedate', 'comment', 'text'], 'safe'],
        [['datetime', 'closedate'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
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
    $query = DoTask::find();

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
        'status' => $this->status,
        'task_id' => $this->task_id,
    ]);

    $query->andFilterWhere(['like', 'comment', $this->comment])
        ->andFilterWhere(['like', 'text', $this->text]);


    //Filter for ranger datetime_from
    if (isset($this->datetime_from)) {
      $query
          ->andFilterWhere(['>=', 'datetime', GridHelper::getDbDateFromDateRangeFormat($this->datetime_from)])
          ->andFilterWhere(['<=', 'datetime', GridHelper::getDbDateFromDateRangeFormat($this->datetime_to, 'P1D')]);
    };

    //Filter for ranger closedate_from
    if (isset($this->closedate_from)) {
      $query
          ->andFilterWhere(['>=', 'closedate', GridHelper::getDbDateFromDateRangeFormat($this->closedate_from)])
          ->andFilterWhere(['<=', 'closedate', GridHelper::getDbDateFromDateRangeFormat($this->closedate_to, 'P1D')]);
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
