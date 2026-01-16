<?php

namespace frontend\modules\cafe\models;

use frontend\modules\franchisee\models\Franchisee;
use frontend\modules\report\models\ReportAutoSend;
use frontend\modules\tariffs\models\Tariffs;
use frontend\modules\tasks\models\Task;
use frontend\modules\templates\models\Template;
use frontend\modules\templates\models\TemplateToCafe;
use frontend\modules\users\models\Users;
use frontend\modules\visits\models\VisitorLog;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;
use yii\validators\NumberValidator;
use yii\validators\RequiredValidator;
use yii\web\UploadedFile;

/**
 * This is the model class for table "cafe".
 *
 * @property int $id
 * @property string $name
 * @property int $max_person
 * @property string $address
 * @property int $last_task
 * @property int $franchisee_id
 * @property string $currency
 * @property int $params_id
 * @property string $vat_code
 * @property int $child_discount
 * @property string $logo
 * @property array $data
 *
 * @property Polls[] $polls
 * @property UserCafe[] $userCafes
 * @property UserTimetable[] $userTimetables
 * @property TemplateToCafe[] $templateToCaves
 * @property Template[] $templates
 */
class Cafe extends \common\components\ActiveRecord
{
  public $new_logo;

  public $role_ids;

  static $default_logo = "/img/logo_fms.png";

  public $_report = false;

  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'cafe';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
        [['name', 'params_id'], 'required'],
        [['params_id'], 'validateParamsId'],
        [['name', 'address'], 'trim'],
        [['selfmode_banner'], 'safe'],
        [['name', 'address', 'currency', 'api_key'], 'string'],
        [['max_person', 'last_task', 'franchisee_id', "width", 'initSuccessful', 'pdf_to_mail'], 'integer'],
        ["logo", "image"],
        ['!new_logo', 'file', 'extensions' => 'png', 'on' => ['insert', 'update']],
        [['new_logo'], 'image',
          //'minWidth' => 200, 'maxWidth' => 200,
          //'minHeight' => 200, 'maxHeight' => 200,
            'maxSize' => 300 * 1024,
            'skipOnEmpty' => true
        ],
        [['vat_code'], 'safe'],
        [['vat_code'], 'validateVatCodeItems'],
        [['role_ids'], 'safe'],
        [['tips_var'], 'safe'],
        [['child_discount'], 'default', 'value' => 0],
        [['child_discount'], 'integer', 'min' => 0, 'max' => 100],
        [['selfservice_timaout'], 'integer', 'min' => 1, 'max' => 100],
        [['selfservice_timaout'], 'default', 'value' => 5],
        [['tips_var'], 'string', 'max' => 100],

        [['data'], 'safe'],
        ['report', 'validateReport'],
    ];
  }

  public function validateReport($attribute)
  {
    $items = $this->$attribute;

    if (!is_array($items)) {
      $items = [];
    }
    if (empty($items)) return true;

    $tot_err = 0;

    foreach ($items as $index => $item) {
      $validator = new EmailValidator();
      $error = null;

      if (empty($item['email'])) {
        $error = Yii::t('app', 'Email can no be empty');
      } else {
        $res = $validator->validate($item['email'], $error);
      }

      if (!empty($error) || !$res) {
        $key = $attribute . '[' . $index . '][email]';
        $this->addError($key, Yii::t('app', 'Not valid email'));
        $tot_err++;
      }
      //var_dump($res);
      //var_dump($item);
    }
    return !!$tot_err;
  }

  public function validateVatCodeItems($attribute)
  {
    //return true;
    $requiredValidator = new RequiredValidator();
    $numberValidator = new NumberValidator();

    $requiredAttributes = [
        'value',
    ];

    foreach ($requiredAttributes as $requiredAttribute) {
      foreach ($this->$attribute as $index => $row) {
        $error = null;
        //$requiredValidator->validate($row[$requiredAttribute], $error);
        if (empty($row[$requiredAttribute])) {
          continue;
        }

        //$numberValidator->validate($row[$requiredAttribute], $error);

        if (!empty($error)) {
          $this->addError($attribute . '[' . $index . ']', $error);//Yii::t('app', 'All values must be filled'));
        }
      }
    }
  }

  public function validateParamsId($attribute)
  {
    if ($this->isNewRecord) return true;
    if ($this->oldAttributes[$attribute] == $this->$attribute) return true;

    $new_tax = CafeParams::find()->where(['id' => $this->$attribute])->asArray()->one();
    $old_tax = CafeParams::find()->where(['id' => $this->oldAttributes[$attribute]])->asArray()->one();

    $new_tax = json_decode($new_tax['vat_list']);
    $old_tax = json_decode($old_tax['vat_list']);

    $has_err = false;
    if (count($new_tax) == count($old_tax)) {
      $new_tax_list = ArrayHelper::map($new_tax, 'name', 'name');
      $old_tax_list = ArrayHelper::map($old_tax, 'name', 'name');
      if (empty(array_diff($new_tax_list, $old_tax_list))) return true;
    }

    $this->addError($attribute, Yii::t('app', 'In the new region should be the same taxes.'));
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
        'id' => Yii::t('app', 'ID'),
        'params_id' => Yii::t('app', 'Params'),
        'name' => Yii::t('app', 'Name'),
        'max_person' => Yii::t('app', 'Max Person'),
        'address' => Yii::t('app', 'Address'),
        'last_task' => Yii::t('app', 'Last Task'),
        'franchisee_id' => Yii::t('app', 'Franchisee'),
        'currency' => Yii::t('app', 'Currency'),
        'vat_code' => Yii::t('app', 'Vat code'),
        'child_discount' => Yii::t('app', 'Child Discount'),
        'logo' => Yii::t('app', 'Logo'),
        'new_logo' => Yii::t('app', 'Logo'),
        'role_ids' => Yii::t('app', 'Rules'),
        'width' => Yii::t('app', 'Check printer width'),
        'selfservice_timaout' => Yii::t('app', 'Selfservice timaout( in seconds)'),
        'tips_var' => Yii::t('app', 'Tips fixed value'),
        'api_key' => Yii::t('app', 'Api key'),
        'report' => Yii::t('app', 'Reports autosend list'),
        'initSuccessful' => Yii::t('app', 'Начальная настройка кафе'),
    ];
  }


  public function beforeValidate()
  {
    if (!parent::beforeValidate()) {
      return false;
    }

    if ($this->isNewRecord) {
      if (empty($this->franchisee_id)) {
        if (Yii::$app->user->isGuest) {
          $this->franchisee_id = 1;
        } else {
          if (!Yii::$app->user->can('AllFranchisee')) {
            $this->franchisee_id = Yii::$app->user->identity->franchisee_id;
          }
          if (!Yii::$app->user->can('CafeSetParam')) {
            $this->params_id = Yii::$app->cafe->paramsId;
            $this->vat_code = Yii::$app->cafe->vatCode;
          } else {
            if (!Yii::$app->user->can('CafeSetParam')) {
              $this->vat_code = Yii::$app->cafe->vatCode;
            }
          }
        }
      }
    }

    if (empty($this->api_key)) $this->api_key = md5(time());

    return true;
  }

  public function validate($attributeNames = null, $clearErrors = true)
  {
    $valid = parent::validate($attributeNames, $clearErrors); // TODO: Change the autogenerated stub

    if (strpos($this->formName(), 'Search') !== false) return $valid;

    $old_attr = $this->getOldAttributes();
    if (
        empty($old_attr['franchisee_id']) ||
        $old_attr['franchisee_id'] != $this->franchisee_id
    ) {
      //$this->addError('franchisee_id','test');
      $count_cafe = Cafe::find()
          ->andWhere(['and',
              ['franchisee_id' => $this->franchisee_id],
              ['<>', 'id', $this->id ? $this->id : 0]
          ])->count();


      $count_cafe++;


      $franchasee = $this->getFranchisee()->one();
      if ($franchasee->max_cafe < $count_cafe) {
        $this->addError('franchisee_id', Yii::t('app', 'The maximum number of cafes reached.', [
            'n' => $count_cafe
        ]));
        return false;
      }
    }


    return $valid;
  }

  public function afterSave($insert, $changedAttributes)
  {
    Yii::$app->cache->delete("cafe_params_" . $this->id);
    $this->saveLogo();

    if ($insert) {
      // Links DEFAULT templates to new Cafe
      $template_types = array_keys(Template::getTypeLabels());
      foreach ($template_types as $template_type) {
        /* @var $template Template */
        $template = $this->findTemplate($template_type);
        if ($template && $template->scope_id == Template::SCOPE_DEFAULT) {
          $this->link('templates', $template, ['type_id' => $template->type_id]);
        }
      }

      $franchiseeRoles = explode(',', $this->franchisee->roles);

      $this->unlinkAll('authItems', true);
      foreach ($franchiseeRoles as $role_id) {
        $role = CafeAuthItem::findOne($role_id);
        if (!$role) continue;
        $this->link('authItems', $role);
      }
    }

    if ($this->_report !== false) {
      $report_ids = [];
      foreach ($this->_report as $report) {
        $r = false;
        if (!empty($report['id'])) {
          $r = ReportAutoSend::find()
              ->where([
                  'id' => $report['id'],
                  'cafe_id' => Yii::$app->cafe->id,
              ])
              ->one();
        }
        if (!$r) {
          $r = new ReportAutoSend;
          $r->cafe_id = Yii::$app->cafe->id;
        }

        $r->email = $report['email'];
        $r->type = $report['type'];

        if ($r->validate() && $r->save()) {
          $report_ids[] = $r->id;
        }
      }
      $to_del = ReportAutoSend::find()
          ->andWhere(['not in', 'id', $report_ids])
          ->all();
      if (count($to_del) > 0) {
        foreach ($to_del as $r) {
          $r->delete();
        }
      };
    }
    parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
  }

  public function afterFind()
  {
    if (!is_array($this->vat_code)) {
      $this->vat_code = json_decode($this->vat_code, true);
    }

    parent::afterFind(); // TODO: Change the autogenerated stub
  }

  public function beforeSave($insert)
  {
    if (is_array($this->vat_code)) {
      $this->vat_code = json_encode($this->vat_code);
    }

    return parent::beforeSave($insert); // TODO: Change the autogenerated stub
  }

  public function findTemplate($type_id)
  {
    $template = $this->getTemplate($type_id)->one();

    if (!$template) {
      $template = Template::findDefault($type_id);
    }

    return $template;
  }

  /**
   * Returns VAT names from CafeParam table
   */
  public function getVatNames($fulldata = false)
  {
    if (!($param = $this->param)) {
      $param = CafeParams::find()->one();
    }
    $param = json_decode($param->vat_list, true);

    if ($fulldata) {
      return $param;
    }

    $vats = ArrayHelper::getColumn($param, 'name');
    if (!empty($vats)) {
      return $vats;
    }

    return [];
  }

  /**
   * Returns VAT accounts
   */
  public function getVatAccounts()
  {
    $accounts = is_array($this->vat_code) ? $this->vat_code : json_decode($this->vat_code, true);
    if (!empty($accounts)) {
      return $accounts;
    }

    return [];
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getAuthAssignments()
  {
    return $this->hasMany(CafeAuthAssignment::className(), ['cafe_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getAuthItems()
  {
    return $this->hasMany(CafeAuthItem::className(), ['name' => 'item_name'])->viaTable('{{%cafe_auth_assignment}}', ['cafe_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getPolls()
  {
    return $this->hasMany(Polls::className(), ['cafe_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUserCafes()
  {
    return $this->hasMany(UserCafe::className(), ['cafe_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getParam()
  {
    return $this->hasOne(CafeParams::className(), ['id' => 'params_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getUserTimetables()
  {
    return $this->hasMany(UserTimetable::className(), ['cafe_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getFranchisee()
  {
    return $this->hasOne(Franchisee::className(), ['id' => 'franchisee_id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getTemplateToCafes()
  {
    return $this->hasMany(TemplateToCafe::className(), ['cafe_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getTemplates()
  {
    return $this->hasMany(Template::className(), ['id' => 'template_id'])->viaTable('{{%template_to_cafe}}', ['cafe_id' => 'id']);
  }

  /**
   * @return \yii\db\ActiveQuery
   */
  public function getTemplate($type_id = null)
  {
    $query = $this->hasOne(Template::className(), ['id' => 'template_id'])
        ->viaTable('{{%template_to_cafe}}', ['cafe_id' => 'id'], function (ActiveQuery $q) use ($type_id) {
          if ($type_id !== null) {
            $q->andOnCondition('{{%template_to_cafe}}.type_id = :type_id', [
                ':type_id' => $type_id,
            ]);
          }

          return $q;
        });

    return $query;
  }

  public function saveLogo()
  {
    $logo = UploadedFile::getInstance($this, 'new_logo');
    if ($logo) {
      $extension = explode('.', $logo->name);
      $extension = $extension[count($extension) - 1];
      $name = time() . '.' . $extension;

      if (!is_readable('img/logos/')) {
        mkdir('img/logos/', 0777, true);
      }

      if ($logo->saveAs('img/logos/' . $name)) {
        if (is_readable('img/logos/' . $this->logo) && is_file('img/logos/' . $this->logo)) {
          unlink('img/logos/' . $this->logo);
        }

        self::getDb()
            ->createCommand()
            ->update($this->tableName(), ['logo' => $name], ['id' => $this->id])
            ->execute();
      };
    }
  }

  public function getLogo()
  {
    return $this->logo ? '/img/logos/' . $this->logo : self::$default_logo;
  }

  public static function getCurrentPersonsCount($cafe_id)
  {
    return VisitorLog::find()
        ->select(['cnt' => new Expression('SUM(1 + guest_m + guest_chi)')])
        ->andWhere([
            'cafe_id' => $cafe_id,
            'finish_time' => null,
        ])
        ->scalar();
  }

  public function getDiscounts()
  {
    $key = Discount::DISCOUNT_KEY;
    return (isset($this->data[$key]) && is_array($this->data[$key])) ? $this->data[$key] : [];
  }

  public function getReport()
  {
    if (empty($this->_report)) {
      $this->_report = ReportAutoSend::find()
          ->where(['cafe_id' => $this->id])
          ->all();
    }
    return $this->_report;
  }

  public function setReport($data)
  {
    $this->_report = $data;
  }

  public function getDiscountInfoline()
  {
    $out = [];
    $out[] = Yii::t('config', 'child discount: {child_discount}%', [
        'child_discount' => $this->child_discount
    ]);

    $franchisee_discounts = $this->franchisee->discounts;
    if (!empty($franchisee_discounts)) {
      $out[] = Yii::t('config', 'franchisee discounts count: {franchisee_discounts}', [
          'franchisee_discounts' => count($franchisee_discounts)
      ]);
    };

    $cafe_discounts = $this->discounts;
    if (!empty($cafe_discounts)) {
      $out[] = Yii::t('config', 'cafe discounts count: {cafe_discounts}', [
          'cafe_discounts' => count($cafe_discounts)
      ]);
    };
    return implode('<br>', $out);
  }

  public function testSuccessful($fullResult = true)
  {
    if (!$fullResult && $this->initSuccessful) return true;

    $cafe_id = $this->id;
    $error = 0;
    if ($fullResult) {
      $data = [
          [
              'title' => 'Base config',
              'description' => 'Base config description',
              'icon' => 'fa fa-gear',
              'buttons' => [
                  [
                      'name' => 'edit',
                      'modal' => true,
                      'href' => '/cafe/admin/update?id=' . $cafe_id
                  ]
              ]
          ],
        // [
        // 'title' => 'Tax Data',
        // 'description' => 'Tax Data description',
        //  'icon' => 'fa fa-pencil',
        //  'buttons' => [
        //       [
        //           'name' => 'edit',
        //           'modal' => true,
        //          'href' => '/cafe/admin/update-vat-accounts?id=' . $cafe_id
        //       ]
        //   ]
        // ],
          [
              'title' => 'Rules',
              'description' => 'Rules description',
              'icon' => 'fa fa-sitemap',
              'buttons' => [
                  [
                      'name' => 'edit',
                      'modal' => true,
                      'href' => '/cafe/admin/update-rules?id=' . $cafe_id
                  ]
              ]
          ],
          [
              'title' => 'Discounts',
              'description' => 'Discounts description',
              'icon' => 'fa fa-gift',
              'buttons' => [
                  [
                      'name' => 'edit',
                      'modal' => true,
                      'href' => '/cafe/admin/update-discounts?id=' . $cafe_id
                  ]
              ],
              'infoline' => $this->discountInfoline
          ],
      ];
    }

    $testModules = [
        'TariffsUpdate' => Tariffs::className(),
        'UsersCreate' => Users::className(),
        'TaskCreate' => [Task::className(), 'task'],
    ];

    foreach ($testModules as $RBAC => $module) {
      //if (!$fullResult || Yii::$app->user->can($RBAC)) {
      if (is_array($module)) {
        if (!empty($module[1]) && !Yii::$app->cafe->can($module[1])) {
          continue;
        }
        $module = $module[0];
      }
      $res = $module::test($fullResult);
      if ($fullResult) {
        if (!empty($res['error'])) $error++;
        if (!Yii::$app->user->can($RBAC)) {
          unset($res['buttons']);
        }
        $data[] = $res;
      } else {
        if (!$res) return false; //если нет тарифа для 1-го часа то кафе не досоздано
      }
      //}
    }

    if ($error == 0 && !$this->initSuccessful) {
      $this->initSuccessful = 1;
      $this->save();
    }

    if ($fullResult) {
      return $data;
    }
    return $this->initSuccessful;
  }
}
