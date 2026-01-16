<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 28.09.18
 * Time: 18:52
 */

use frontend\modules\shop\models\ShopSale;
use yii\bootstrap\ActiveForm;

/* @var $model ShopSale */

?>
<?php $form = ActiveForm::begin(); ?>

  <h5><?= Yii::t('main', 'Not specified email address. To send specify it'); ?></h5>

<?= $form->field($model, 'anonymous_email')->textinput()->label(false); ?>

<?php ActiveForm::end(); ?>