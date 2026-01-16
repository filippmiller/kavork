<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 19.10.18
 * Time: 16:45
 */

namespace frontend\modules\selfservice\models;

use frontend\modules\visits\models\VisitorLog;
use frontend\modules\visitor\models\Visitor;
use Yii;
use yii\base\Model;

class SelfServiceUserQrInput extends Model
{

    public $code;
    protected $visitor_id;
    public $visit;
    public $guest_m = 0;
    public $guest_chi = 0;

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('app', 'Code')
        ];
    }


    public function rules()
    {
        return [
            [['code'], 'required'],
            [['code'], 'string', 'max' => 255],
            ['code', 'validateCode'],
        ];
    }

    public function validateCode($attribute, $params, $validator)
    {

        $visitor = Visitor::findOne(['code' => $this->code]);
        if (!$visitor) {
            $this->addError('code', Yii::t('app', 'Wrong QR-code'));
        } else {
            $this->visitor_id = $visitor->id;

            $exists = VisitorLog::find()->where([
                'visitor_id' => $visitor->id,
                'finish_time' => null,
            ])->exists();

            if ($exists) {
                $this->addError('code', Yii::t('app', 'User already in cafe'));
            }
        }
    }

    public function start()
    {
        if (!$this->validate()) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $visit = new VisitorLog();

            $visit->type = VisitorLog::TYPE_REGULAR;

            $visit->visitor_id = $this->visitor_id;
            $visit->cafe_id = Yii::$app->cafe->getId();
            $visit->user_id = Yii::$app->user->id;

            $visit->guest_m = $this->guest_m;
            $visit->guest_chi = $this->guest_chi;

            if ($visit->save()) {
                $transaction->commit();

                $this->visit = $visit;

                return true;
            }

            $transaction->rollBack();

        } catch (\Exception $exception) {
            $transaction->rollBack();
        }

        return false;
    }


}