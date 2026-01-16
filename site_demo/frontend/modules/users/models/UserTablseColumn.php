<?php

namespace frontend\modules\users\models;

use common\components\ActiveRecord;
use Yii;

/**
 * This is the model class for table "cw_users_social".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $cafe_id
 */
class UserTablseColumn extends ActiveRecord
{


  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'user_column_table';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['user_id'], 'required'],
        [['user_id'], 'integer'],
        [['table_name', 'columns_show'], 'string']
    ];
  }

  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'user_id' => Yii::t('app', 'User'),
        'columns_show' => 'columns_show',
        'table_name' => 'table_name',
    ];
  }

  public static function getActiveColumn($table)
  {
    if (Yii::$app->user->isGuest) return false;
    $col = UserTablseColumn::find()->where(['user_id' => Yii::$app->user->id, 'table_name' => $table])->one();
    if (!$col) return [];

    return explode(',', $col->columns_show);
  }

  public static function setActiveColumn($table, $cols)
  {
    if (Yii::$app->user->isGuest) return false;
    $col = UserTablseColumn::find()->where(['user_id' => Yii::$app->user->id, 'table_name' => $table])->one();

    if (!$col) {
      $col = new UserTablseColumn();
      $col->user_id = Yii::$app->user->id;
      $col->table_name = $table;
    };
    $col->columns_show = implode(',', $cols);
    $col->save();
  }
}

