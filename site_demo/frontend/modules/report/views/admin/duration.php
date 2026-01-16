<?php


?>
  <div class="none_pr">
    <div class="duration"><?php echo Yii::t('report', 'Duration statistics')?></div>
	<?php if (!empty($sum_table['pay'])) { ?>
	<div class="row flex_tile">
    <div class="col-md-6 dur_tbl">
      <div class="info_block" style="display: none; top: 103px;">
        <div class="sel_p"><?php echo Yii::t('report', 'Selected')?></div>
        <div class="row">
          <div class="col-md-6 person"><span>49</span></div>
          <div class="col-md-6 paral"><?php echo Yii::t('main', 'person(s)')?></div>
        </div>
        <div class="row">
          <div class="col-md-6 dolia"><span>13.84</span></div>
          <div class="col-md-6 paral"> %</div>
        </div>
        <div class="col-md-12 sel_p" style="margin-bottom:9px;"></div>
        <button onclick="clear_sel_dur()" type="button" class="btn btn-warning"><span class="fa fa-times"></span> <?php echo Yii::t('report', 'Clear select')?> </button>
      </div>
      <table class="table table-bordered duration_table">
        <thead>
        <tr>
          <th colspan="2"><?php echo Yii::t('report', 'HOURS')?></th>
          <th colspan="2"><?php echo Yii::t('report', 'VISITORS')?></th>
        </tr>
        <tr>
          <th><?php echo Yii::t('report', 'Greater then')?></th>
          <th><?php echo Yii::t('report', 'Less then or equal to')?></th>
          <th><?php echo Yii::t('report', 'Number of visitor')?></th>
          <th><?php echo Yii::t('report', '% of visitor')?></th>
        </tr>
        </thead>
        <?php foreach ($sum_table['pay'] as $item) { ?>
          <tr code="<?=$item['gr'];?>" cnt="<?=$item['cnt'];?>" pers="<?=$item['val'];?>">
            <td><?=$item['gr'];?></td>
            <td><?=$item['to'];?></td>
            <td><?=$item['cnt'];?></td>
            <td><?=$item['val'];?> %</td>
          </tr>
        <?php } ?>
        <tfoot>
        <tr>
          <td><b><?php echo Yii::t('report', 'Total')?></b></td>
          <td></td>
          <td><b><?= $sum_table['total']; ?></b></td>
          <td><b>100 %</b></td>
        </tr>
        </tfoot>
      </table>
	</div>
    <div id="charts-duration" class="col-md-6"></div>
  </div>
  <?php }; ?>
 </div>
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
