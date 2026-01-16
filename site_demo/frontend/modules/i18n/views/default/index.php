<?php
$this->title = Yii::t('app', 'Translations');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
  <div class="col-md-2" style="margin-top:10px;">
    <div style="display: inline-flex;align-items: center;"><?= Yii::t('app', 'Page') ?>:&nbsp;<select
          class="form-control" style="width:60px;" name="page"></select>&nbsp;<?= Yii::t('app', 'of') ?>&nbsp;<span
          class="page_count">0</span></div>
  </div>
  <div class="col-md-2" style="margin-top:10px;">
    <select class="form-control" name="category">
      <option selected value=""><?= Yii::t('app', 'Category') ?></option>
    </select>
  </div>
</div>
<hr class="hrmin">
<table class="table table-condensed table-hover" id="lg_table">
  <thead>
  <tr>
    <th code="file">
      <?= Yii::t('app', 'Category') ?>
    </th>
    </th>
    <th code="code">
      <?= Yii::t('app', 'System') ?>
    </th>
    <?php foreach ($lg_list as $code => $name) {
      ?>
      <th code="<?= $code; ?>"><?= $name; ?> (<?= $code; ?>
        )<?= $code == $defaultLang ? '<small> ' . Yii::t('app', 'Default') . '</small>' : ''; ?></th>
      <?php
    }
    ?>
  </tr>
  </thead>
  <tbody></tbody>
</table>

<script>
  var lg_base = <?=json_encode($lg_base);?>;
  var lg_list = <?=json_encode($lg_list);?>;
  var lg_default = '<?=$defaultLang;?>';
</script>