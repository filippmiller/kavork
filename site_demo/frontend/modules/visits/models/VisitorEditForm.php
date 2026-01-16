<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 13.09.18
 * Time: 17:49
 */

namespace frontend\modules\visits\models;

use frontend\modules\visitor\models\Visitor;

class VisitorEditForm extends Visitor
{
  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['f_name', 'code'], 'required'],
        [['f_name', 'l_name', 'code', 'email', 'phone', 'notice', 'lg'], 'string'],
        [['franchisee_id'], 'integer'],
        [['f_name', 'l_name', 'code', 'email', 'phone', 'lg'], 'trim'],

        [['email'], 'email'],
        [['code'], 'unique']
    ];
  }
}