<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

/* Объявляем класс формы */
class ContactForm extends Model
{
  /* Объявление переменных */
  public $name, $email, $body, $phone;

  /* Правила для полей формы обратной связи (валидация) */
  public function rules()
  {
    return [
      /* Поля в которых в начале и в конце удаляются пробелы */
        [ ['name', 'email', ], 'trim'],
      /* Поля обязательные для заполнения */
        [ ['name', 'email', ], 'required'],
      /* Просто текстовые поля */
        [['phone', 'body'],'safe'],
      /* Поле электронной почты */
        ['email', 'email'],
    ];
  }

  /* Определяем названия полей */
  public function attributeLabels()
  {
    return [
        'name' => Yii::t('landing', 'name'),
        'email' => Yii::t('landing', 'email'),
        'phone' => Yii::t('landing', 'phone'),
        'body' => Yii::t('landing', 'content'),
    ];
  }

  /* функция отправки письма на почту */
  public function contact($emailto)
  {

    $body = Yii::t('landing', 'Sender name').": " .$this->name." (".$this->email.")\n";
    if(!empty($this->phone))$body .= Yii::t('landing', 'phone').": " .$this->phone."\n";
    if(!empty($this->body))$body .= Yii::t('landing', 'Sender content').": " .$this->body."\n";

    /* Проверяем форму на валидацию */
    if ($this->validate()) {
      Yii::$app->mailer->compose()
          ->setFrom(Yii::$app->params['robotEmail']) /* от кого */
          ->setTo($emailto) /* куда */
          ->setSubject(Yii::t('landing', 'Letter subject')) /* имя отправителя */
          ->setTextBody($body) /* текст сообщения */
          ->send(); /* функция отправки письма */

      return true;
    } else {
      return false;
    }
  }
}
