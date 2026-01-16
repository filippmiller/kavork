<?php

namespace frontend\modules\cafe\models;

use Yii;

/**
 * This is the model class for table "{{%cafe_auth_item}}".
 *
 * @property string $name
 * @property string $parent
 * @property string $description
 * @property int $created_at
 * @property int $updated_at
 *
 * @property CafeAuthAssignment[] $cafeAuthAssignments
 * @property Cafe[] $cafes
 */
class CafeAuthItem extends \common\components\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return '{{%cafe_auth_item}}';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['name'], 'required'],
        [['description'], 'string'],
        [['created_at', 'updated_at', 'sort_index'], 'integer'],
        [['name', 'parent'], 'string', 'max' => 64],
        [['name'], 'unique'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'name' => Yii::t('app', 'Name'),
        'parent' => Yii::t('app', 'Parent'),
        'description' => Yii::t('app', 'Description'),
        'created_at' => Yii::t('app', 'Created At'),
        'updated_at' => Yii::t('app', 'Updated At'),
    ];
  }

  /**
   * Return array of possible Cage roles with name => name
   */
  public static function getList($index = null)
  {
    $roles = self::find()
        ->orderBy('sort_index')
        ->asArray()
        ->all();

    $list = [];

    foreach ($roles as $role) {
      $list[$role['name']] = Yii::t('app', $role['description']);
    }

    if ($index !== null) {
      return (isset($list[$index])) ? $list[$index] : Yii::t('app', 'Unknown');
    }

    return $list;
  }

  /**
   * Return tree of possible Cage roles with name => name
   */
  public static function getTree($allowedRoles = null)
  {
    $rolesQuery = self::find()
        ->orderBy('sort_index');

    if ($allowedRoles !== null) {
      $rolesQuery->andWhere(['name' => $allowedRoles]);
    }

    $roles = $rolesQuery->asArray()->all();

    $list = [];

    $iterator = function (&$data, $name = null) use (&$list, &$iterator) {
      $tmpArray = [];
      foreach ($data as $index => $role) {
        if ($role['parent'] == $name) {

          $childrens = $iterator($data, $role['name']);

          $roleData = [
              'name' => $role['name'],
              'description' => Yii::t('app', $role['description']),
              'children' => [],
          ];

          if (!empty($childrens)) {
            $roleData['children'] = $childrens;
          }

          $tmpArray[] = $roleData;
        }
      }

      return $tmpArray;
    };

    $list = $iterator($roles);

    return $list;
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCafeAuthAssignments()
  {
    return $this->hasMany(CafeAuthAssignment::className(), ['item_name' => 'name']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getCafes()
  {
    return $this->hasMany(Cafe::className(), ['id' => 'cafe_id'])->viaTable('{{%cafe_auth_assignment}}', ['item_name' => 'name']);
  }
}
