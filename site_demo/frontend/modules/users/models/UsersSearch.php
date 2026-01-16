<?php

namespace frontend\modules\users\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * UsersSearch represents the model behind the search form of `frontend\modules\users\models\Users`.
 */
class UsersSearch extends Users
{

  public $cafe;

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'state', 'franchisee_id', 'cafe'], 'integer'],
        ['lg', 'string'],
        [['name', 'pass', 'last_sess', 'email', 'color', 'role'], 'safe'],
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
    $query = Users::find();

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
        'lg' => $this->lg,
        'state' => $this->state,
        'franchisee_id' => $this->franchisee_id,
    ]);

    if ($this->role && strlen(trim($this->role)) > 0) {
      $query->leftJoin('auth_assignment', 'user.id= auth_assignment.user_id');
      if ($this->role == -1) {
        $query->andWhere('auth_assignment.item_name  IS NULL');
      } else {
        $query->andWhere('auth_assignment.item_name=\'' . $this->role . '\'');
      }
    }

    if ($this->cafe && strlen(trim($this->cafe)) > 0 || !Yii::$app->user->can('AllCafeShow')) {
      $query->leftJoin('user_cafe', 'user.id= user_cafe.user_id');

      if ($this->cafe && strlen(trim($this->cafe)) > 0) {
        $query->andWhere('user_cafe.cafe_id=\'' . $this->cafe . '\'');
      }

      if(!Yii::$app->user->can('AllCafeShow')){
        $cafes = array_keys(ArrayHelper::index(Users::getCafesList(),'id'));
        $query->andWhere('user_cafe.cafe_id in (' . implode($cafes,',') . ')');
      }
    }

    $query->andFilterWhere(['like', '.name', $this->name])
        ->andFilterWhere(['like', '.email', $this->email])
        ->andFilterWhere(['like', '.color', $this->color]);

    return $dataProvider;
  }
}
