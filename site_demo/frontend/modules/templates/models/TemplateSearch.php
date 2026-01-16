<?php

namespace frontend\modules\templates\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * TemplateSearch represents the model behind the search form of `frontend\modules\templates\models\Template`.
 */
class TemplateSearch extends Template
{
  public $_used_in_cafe;

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['id', 'scope_id', 'cafe_id', 'cafe_id', 'franchisee_id', 'type_id', 'updated_at', 'created_at'], 'integer'],
        [['content'], 'safe'],
        [['_used_in_cafe'], 'safe'],
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
    $query = Template::find();
    $query->alias('t');
    $query->joinWith('templateToCafes');
    // Active first
    $query->orderBy(new Expression('({{%template_to_cafe}}.template_id IS NOT NULL) DESC'));

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

    if (!empty($this->_used_in_cafe)) {
      if ($this->_used_in_cafe == 1) {
        $query->andWhere('{{%template_to_cafe}}.template_id IS NOT NULL');
      } elseif ($this->_used_in_cafe == 2) {
        $query->andWhere('{{%template_to_cafe}}.template_id IS NULL');
      }
    }

    $query->andWhere([
        'OR',
        [
            't.scope_id' => self::SCOPE_DEFAULT,
        ],
        [
            't.cafe_id' => Yii::$app->cafe->getId(),
        ],
        [
            't.franchisee_id' => Yii::$app->cafe->getFranchiseeId(),
        ],
    ]);

    // grid filtering conditions
    $query->andFilterWhere([
        't.id' => $this->id,
        't.scope_id' => $this->scope_id,
        't.cafe_id' => $this->cafe_id,
        't.franchisee_id' => $this->franchisee_id,
        't.type_id' => $this->type_id,
        't.updated_at' => $this->updated_at,
        't.created_at' => $this->created_at,
    ]);

    $query->andFilterWhere(['like', 't.content', $this->content]);

    return $dataProvider;
  }
}
