<?php

namespace frontend\modules\polls\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * PollsSearch represents the model behind the search form of `frontend\modules\polls\models\Polls`.
 */
class PollsSearch extends Polls
{


  public function behaviors()
  {
    return [
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "created",
            "dateStartAttribute" => "created_from",
            "dateEndAttribute" => "created_to",
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
        [['id', 'status', 'user_id', 'other_ans', 'user_status', 'event', 'cafe_id', 'is_poll',], 'integer'],
        [['question', 'answers', 'created'], 'safe'],
        [['created'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
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
    $query = Polls::find();

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
      $query->andFilterWhere(['user_id'=>$user_id]);
    }


    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'status' => $this->status,
        'other_ans' => $this->other_ans,
        'user_status' => $this->user_status,
        'event' => $this->event,
        'cafe_id' => Yii::$app->cafe->id,
        'is_poll' => $this->is_poll,
    ]);

    $query->andFilterWhere(['like', 'question', $this->question])
        ->andFilterWhere(['like', 'answers', $this->answers]);


    //Filter for ranger created_from
    if (isset($this->created_from)) {
      $query
          ->andFilterWhere(['>=', 'created', GridHelper::getDbDateFromDateRangeFormat($this->created_from)])
          ->andFilterWhere(['<=', 'created', GridHelper::getDbDateFromDateRangeFormat($this->created_to, 'P1D')]);
    };
    return $dataProvider;
  }

}
