<?php

namespace frontend\modules\mails\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * TemplateMailSearch represents the model behind the search form of `frontend\modules\mails\models\TemplateMail`.
 */
class TemplateMailSearch extends TemplateMail
{
  public $cafe_id_from;
  public $cafe_id_to;
  public $updated_at_from;
  public $updated_at_to;
  public $created_at_from;
  public $created_at_to;

  private $slideParams = array(
      'cafe_id' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
      'updated_at' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
      'created_at' =>
          array(
              'min' => 0,
              'max' => 100,
              'step' => 1,
          ),
  );

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'cafe_id', 'cafe_id_from', 'cafe_id_to', 'updated_at', 'updated_at_from', 'updated_at_to', 'created_at', 'created_at_from', 'created_at_to'], 'integer'],
        [['content', 'user_filter', 'status', 'title'], 'safe'],
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
    $query = TemplateMail::find();

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

    /*$user_id=$this->user_id;
    if($user_id==0){
      $user_id=null;
    }else if($this->user_id==-1){
      $query->andFilterWhere(['is','user_id',(new Expression('Null'))]);
    }else if($user_id){
     $query->andFilterWhere(['user_id'=>new Expression('Null')]);
    }*/


    // grid filtering conditions
    $query->andFilterWhere([
        'id' => $this->id,
        'cafe_id' => Yii::$app->cafe->id,
        'updated_at' => $this->updated_at,
        'created_at' => $this->created_at,
    ]);

    $query->andFilterWhere(['like', 'content', $this->content])
        //->andFilterWhere(['like', 'user_filter', $this->user_filter])
        ->andFilterWhere(['like', 'title', $this->title])
        ->andFilterWhere(['like', 'status', $this->status]);


    //Filter for ranger cafe_id_from
    if (is_numeric($this->cafe_id_from)) {
      $query
          ->andFilterWhere(['>=', 'cafe_id', (float)$this->cafe_id_from])
          ->andFilterWhere(['<=', 'cafe_id', (float)$this->cafe_id_to]);
    };

    //Filter for ranger updated_at_from
    if (is_numeric($this->updated_at_from)) {
      $query
          ->andFilterWhere(['>=', 'updated_at', (float)$this->updated_at_from])
          ->andFilterWhere(['<=', 'updated_at', (float)$this->updated_at_to]);
    };

    //Filter for ranger created_at_from
    if (is_numeric($this->created_at_from)) {
      $query
          ->andFilterWhere(['>=', 'created_at', (float)$this->created_at_from])
          ->andFilterWhere(['<=', 'created_at', (float)$this->created_at_to]);
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
