<?php

use frontend\modules\cafe\models\CafeParams;

$dates_format = [
    CafeParams::DATE_MM_DD_YYYY => Yii::t('app', "MM DD YYYY"),
    CafeParams::DATE_DD_MM_YYYY => Yii::t('app', "DD MM YYYY"),
    CafeParams::DATE_YYYY_MM_DD => Yii::t('app', "YYYY MM DD"),
];
$weekday = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
];

if (empty($model)) {
  $model = CafeParams::find()->one();
}

$vatPossibleNames = json_decode($model->vat_list, true);
?>

<div class="bef_time one_header"><?= Yii::t('app', 'Region data'); ?></div>
<?php
foreach ($vatPossibleNames as $vatName) {
  ?>
  <div class="bef_time"><?= $vatName['name']; ?> : <span><?= $vatName['value']; ?>%</span></div>
<?php } ?>
<div class="bef_time">
  <?= $model->getAttributeLabel('time_zone'); ?> : <span><?= Yii::$app->params['timeZone'][$model->time_zone]; ?></span>
</div>

<div class="bef_time">
  <?= $model->getAttributeLabel('date_format'); ?> : <span><?= $dates_format[$model->date_format]; ?></span>
</div>

<div class="bef_time">
  <?= $model->getAttributeLabel('first_weekday'); ?> :
  <span><?= Yii::t('app', $weekday[$model->first_weekday]); ?></span>
</div>

<div class="bef_time">
  <?= $model->getAttributeLabel('time_format'); ?> :
  <span><?= $model->time_format == CafeParams::TIME_12 ? 12 : 24; ?> <?= Yii::t('app', 'Hours'); ?></span>
</div>

<div class="bef_time"><?= $model->getAttributeLabel('banknote_list'); ?> : <span><?= $model->banknote_list; ?></span>
</div>

