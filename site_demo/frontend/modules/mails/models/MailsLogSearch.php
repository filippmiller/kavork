<?php

namespace frontend\modules\mails\models;

use app\helpers\GridHelper;
use kartik\daterange\DateRangeBehavior;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * MailsLogSearch represents the model behind the search form of `frontend\modules\mails\models\MailsLog`.
 */
class MailsLogSearch extends MailsLog
{
  public $cteated_at_from;
  public $cteated_at_to;
  public $last_visitor_id_from;
  public $last_visitor_id_to;
  public $mail_id_from;
  public $mail_id_to;
  public $user_id_from;
  public $user_id_to;
  public $cafe_id_from;
  public $cafe_id_to;
  public $count_from;
  public $count_to;

  private $slideParamsDb = true;
  private $slideParams = array(
      'last_visitor_id' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
      'mail_id' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
      'user_id' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
      'cafe_id' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
      'count' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
  );

  public function behaviors()
  {
    return [
        [
            "class" => DateRangeBehavior::className(),
            "attribute" => "cteated_at",
            "dateStartAttribute" => "cteated_at_from",
            "dateEndAttribute" => "cteated_at_to",
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
        [['id', 'last_visitor_id', 'last_visitor_id_from', 'last_visitor_id_to', 'mail_id', 'mail_id_from', 'mail_id_to', 'user_id', 'user_id_from', 'user_id_to', 'cafe_id', 'cafe_id_from', 'cafe_id_to', 'count', 'count_from', 'count_to', 'status'], 'integer'],
        [['name', 'cteated_at', 'content', 'params'], 'safe'],
        [['cteated_at'], 'match', 'pattern' => '/^.+\s\-\s.+$/'],
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
    $query = MailsLog::find();

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
        'last_visitor_id' => $this->last_visitor_id,
        'mail_id' => $this->mail_id,
        'cafe_id' => Yii::$app->cafe->id,
        'status' => $this->status,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
        ->andFilterWhere(['like', 'content', $this->content])
        ->andFilterWhere(['like', 'params', $this->params]);


    //Filter for ranger last_visitor_id_from
    if (is_numeric($this->last_visitor_id_from)) {
      $query
          ->andFilterWhere(['>=', 'last_visitor_id', (float)$this->last_visitor_id_from])
          ->andFilterWhere(['<=', 'last_visitor_id', (float)$this->last_visitor_id_to]);
    };

    //Filter for ranger mail_id_from
    if (is_numeric($this->mail_id_from)) {
      $query
          ->andFilterWhere(['>=', 'mail_id', (float)$this->mail_id_from])
          ->andFilterWhere(['<=', 'mail_id', (float)$this->mail_id_to]);
    };

    //Filter for ranger user_id_from
    if (is_numeric($this->user_id_from)) {
      $query
          ->andFilterWhere(['>=', 'user_id', (float)$this->user_id_from])
          ->andFilterWhere(['<=', 'user_id', (float)$this->user_id_to]);
    };

    //Filter for ranger cafe_id_from
    if (is_numeric($this->cafe_id_from)) {
      $query
          ->andFilterWhere(['>=', 'cafe_id', (float)$this->cafe_id_from])
          ->andFilterWhere(['<=', 'cafe_id', (float)$this->cafe_id_to]);
    };

    //Filter for ranger count_from
    if (is_numeric($this->count_from)) {
      $query
          ->andFilterWhere(['>=', 'count', (float)$this->count_from])
          ->andFilterWhere(['<=', 'count', (float)$this->count_to]);
    };

    //Filter for ranger cteated_at_from
    if (isset($this->cteated_at_from)) {
      $query
          ->andFilterWhere(['>=', 'cteated_at', GridHelper::getDbDateFromDateRangeFormat($this->cteated_at_from)])
          ->andFilterWhere(['<=', 'cteated_at', GridHelper::getDbDateFromDateRangeFormat($this->cteated_at_to, 'P1D')]);
    };
    return $dataProvider;
  }

  public function getSlideParams($name)
  {
    if ($this->slideParamsDb) {
      $query = MailsLog::find();

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
