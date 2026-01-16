<?php

use yii\helpers\Html;
use frontend\modules\users\models\Users;
use frontend\components\GridView;

$columns = [
    [
        'label'  => Yii::t('app', 'Admin'),
        'format' => 'raw',
        'value'  => function ($data) {
            $model = Users::find()->where(['id' => $data['user_id']])->asArray()->one();
            if ($model) {
                return $model['name'];
            }

            return null;
        },
    ],
];

// For Each Day
foreach ($days as $day) {
    $columns[] = [
        'label'  => Yii::t('app', $day),
        'format' => 'raw',
        'value'  => function ($data) use ($day) {
            if (isset($data[$day])) {
                return Yii::$app->helper->echo_time($data[$day]);
            }

            return null;
        },
    ];
}

// Summary
$columns[] = [
    'label'  => Yii::t('app', 'Summary'),
    'format' => 'raw',
    'value'  => function ($data) use ($days) {
        $sum = 0;
        foreach ($days as $day) {
            if (isset($data[$day])) {
                $sum += $data[$day];
            }
        }

        if ($sum > 0) {
            return Yii::$app->helper->echo_time($sum);
        }

        return null;
    },
];

?>
<div class="row margin-bottom-10 padding-bottom-10">
  <div class="col-md-offset-3 col-md-6 text-center">
    <div class="control_week input-group bootstrap-touchspin">
      <span class="input-group-btn">
            <?= Html::a('<i class="fa fa-angle-double-left"></i>', ['/users/report/index', 'date' => $date_next],
                ['role' => 'modal-remote', 'class' => 'btn btn-science-blue']); ?>
      </span>
      <div class="control_week_date"><?= $date_start; ?> - <?= $date_end; ?></div>
      <span class="input-group-btn">
      <?= Html::a('<i class="fa fa-angle-double-right"></i>', ['/users/report/index', 'date' => $date_prev],
          ['role' => 'modal-remote', 'class' => 'btn btn-science-blue']); ?>
      </span>
    </div>
  </div>
</div>
<div class="row">
    <div class="col-xs-12 table_report">
      <table class="table table-bordered table-hover" id="week_report">
        <tr>
          <td class="font-weight-700"><?=Yii::t('app','ADMIN');?></td>
          <?php foreach ($days as $day => $weakday){?>
            <td class="font-weight-600">
              <?=$weakday;?><br>
              <?=$day;?>
            </td>
          <?php };?>
          <td class="font-weight-700 text-info">
            <?=Yii::t('app','TOTAL FOR THE WEEK');?><br>
            <?=$date_start;?> - <?=$date_end;?>
          </td>
        </tr>
        <?php foreach ($model as $user_id => $durations){?>
            <tr>
              <td class="font-weight-700 text-info">
                <?=empty($users[$user_id])?'-':$users[$user_id];?>
              </td>
          <?php foreach ($days as $day => $weakday){?>
            <td>
              <?=empty($durations[$day])?'-':Yii::$app->helper->echo_time($durations[$day]);?><br>
            </td>
          <?php };?>
              <td class="font-weight-700 text-info">
                <?=empty($total[$user_id])?'-':Yii::$app->helper->echo_time($total[$user_id]);?><br>
              </td>
            </tr>
        <?php };?>

      </table>
  </div>
</div>