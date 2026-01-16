<?php


?>
  <div class="row">
    <div class="col-md-12">
      <div id="bayDay"></div>
    </div>
  </div>
  <div class="row">
    <div class="col-md-8">
      <div id="bayHour"></div>
    </div>
    <div class="col-md-4">
      <div id="bayWeekday"></div>
    </div>
  </div>
  <div class="none_pr">
    <div class="col-sm-3 col-xs-6 nach">
	<div class="table-responsive"> 
      <table class="table">
        <tr>
          <th colspan="2">&nbsp;</th>
        </tr>
        <tr>
          <td rowspan="4" class="vertikal_text oxi">
            <font style="color:#7BAD18;"><span class="glyphicon glyphicon-ok"></span></font> <?= Yii::t('report', 'PAY') ?>
          </td>
          <td><?= Yii::t('app', 'Person') ?></td>
        </tr>
        <tr>
          <td><?= Yii::t('app', 'Sum') ?></td>
        </tr>
        <tr>
          <td><?= Yii::t('app', 'Tax') ?></td>
        </tr>
        <tr>
          <td class="oxi"><?= Yii::t('app', 'Total') ?></td>
        </tr>
        <tr>
          <td rowspan="4" class="vertikal_text oxi">
            <font style="color:#FA6800;"><span class="glyphicon glyphicon-remove"></span></font> <?= Yii::t('report', 'NOT PAY') ?>
          </td>
          <td><?= Yii::t('app', 'Person') ?></td>
        </tr>
        <tr>
          <td><?= Yii::t('app', 'Sum') ?></td>
        </tr>
        <tr>
          <td><?= Yii::t('app', 'Tax') ?></td>
        </tr>
        <tr>
          <td class="oxi"><?= Yii::t('app', 'Total') ?></td>
        </tr>
        <?php if (Yii::$app->cafe->can('shopAll')) { ?>
          <tr>
            <td rowspan="3" class="vertikal_text oxi">
              <font style="color:#FBBB0F;"><span class="fa fa-shopping-cart"></span></font> <?= Yii::t('report', 'SHOP') ?>
            </td>
            <td><?= Yii::t('app', 'Sum') ?></td>
          </tr>
          <tr>
            <td><?= Yii::t('app', 'Tax') ?></td>
          </tr>
          <tr>
            <td class="oxi"><?= Yii::t('app', 'Total') ?></td>
          </tr>
        <?php }; ?>
      </table>
	  </div>
    </div>
    <div class="col-sm-9 col-xs-6 nach_tab boliwood">
<div class="table-responsive"> 
      <table class="table text-right">
        <tr>
          <?php foreach ($sum_table['pay'] as $th) {
            echo '<th class="text-right" style="white-space: nowrap;">';
            if (count($sum_table['pay']) > 1) echo $th['gr'];
            else echo Yii::t('app', 'Total');
            echo '</th>';
          } ?>
        </tr>

        <?php
        $row = ['pay', 'not_pay'];
        if (Yii::$app->cafe->can('shopAll')) {
          $row[] = 'shop';
        }
        foreach ($row as $pay_type) {
          if ($pay_type != 'shop') {
            ?>
            <tr>
              <?php foreach ($sum_table[$pay_type] as $td) {
                echo '<td>' . ($td['cnt'] + $td['guest_m'] + $td['guest_chi']) . '</td>';
              } ?>
            </tr>
            <?php
          }
          foreach (['sum', 'tax', 'cost'] as $col) { ?>
            <tr>
              <?php foreach ($sum_table[$pay_type] as $td) {
                echo '<td>' . round($td[$col], 2) . ' ' . $currency . '</td>';
              } ?>
            </tr>
          <?php } ?>
        <?php } ?>
      </table>
	  </div>
    </div>
  </div>


<?php if (!empty($isSummary)) {
  ; ?>
  <div>
    <center>
      <font style="color:#FA6800;"><span class="glyphicon glyphicon-remove"></span> </font>
      <font style="font-weight:600;"><?= Yii::t('report', 'Table not paid') ?></font>
    </center>
  </div>
  <div class="table-responsive"> 
  <table class="table">
    <tr>
      <th><?= Yii::t('report', 'Name') ?></th>
      <th><?= Yii::t('app', 'Phone') ?></th>
      <th><?= Yii::t('app', 'Email') ?></th>
      <th><?= Yii::t('app', 'Notice') ?></th>
      <th class="text-center"><?= Yii::t('report', 'Qty not paid') ?></th>
      <th class="text-center" style="white-space: nowrap;"><?= Yii::t('app', 'Total Qty') ?></th>
      <th><?= Yii::t('app', 'Cost') ?></th>
      </th>
      <th><?= Yii::t('app', 'Time') ?></th>
      </th>
      <th><?= Yii::t('app', 'User ID') ?></th>
      </th>
    </tr>
    <?php if (!empty($not_pay_user)) { ?>
      <?php foreach ($not_pay_user as $visit) { ?>
        <tr>
          <?php if ($visit->visitor_id > 0) { ?>
            <td><?= $visit->visitor->f_name; ?> <?= $visit->visitor->l_name; ?></td>
            <td><?= $visit->visitor->phone; ?></td>
            <td><?= $visit->visitor->email; ?></td>
          <?php } else { ?>
            <td colspan="3">
              <?= Yii::t('app', "Anonymous"); ?>
            </td>
          <?php }; ?>
          <td><?= $visit->comment; ?></td>
          <?php if ($visit->visitor_id > 0) { ?>
            <td class="text-center"><?= $visit->visitor->getQty(['pay_state' => -1]); ?></td>
            <td class="text-center"><?= $visit->visitor->qty; ?></td>
          <?php } else { ?>
            <td colspan="2"></td>
          <?php }; ?>
          <td style="white-space: nowrap;"><?= round($visit->cost, 2); ?> <?= $currency; ?></td>
          <td><?= Yii::$app->helper->echo_time($visit->duration); ?></td>
          <td><?= $visit->user->name; ?></td>
        </tr>
      <?php } ?>
    <?php } else { ?>
      <tr>
        <td>-</td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
        <td class="text-center">-</td>
        <td class="text-center">-</td>
        <td>-</td>
        <td>-</td>
        <td>-</td>
      </tr>
    <?php }; ?>
  </table>
  </div>
  <div class="col-xs-12 nach_tab" style="padding-top: 20px;">
    <div>
      <center><font style="color:#006AC1;"><span class="glyphicon glyphicon-user"></span> </font> <font
            style="font-weight:600;"> <?= Yii::t('report', 'Staff session table') ?></font>
        <center></center>
      </center>
    </div>
	<div class="table-responsive"> 
    <table class="table">
      <tbody>
      <tr>
        <th><?= Yii::t('app', 'User ID') ?></th>
        </th>
        <th><?= Yii::t('app', 'Time') ?></th>
      </tr>
      <?php if (!empty($admins)) { ?>
        <?php foreach ($admins as $admin) { ?>
          <tr>
            <td><?= $admin['name']; ?></td>
            <td><?= Yii::$app->helper->echo_time($admin['duration']); ?></td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr>
          <td>-</td>
          <td>-</td>
        </tr>
      <?php }; ?>
      </tbody>
    </table>
	</div>
  </div>
<?php }; ?>