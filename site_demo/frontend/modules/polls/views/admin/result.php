<?php


?>

  <div id="charts-result"></div>

<?php if (count($other_ans) > 0) { ?>
  <h5><?= Yii::t('app', 'Own answers'); ?></h5>
  <table class="table table-striped table-bordered">
    <tbody>
    <tr>
      <th><?= Yii::t('app', 'User name'); ?></th>
      <th><?= Yii::t('app', 'Answer'); ?></th>
    </tr>
    <?php foreach ($other_ans as $ans) { ?>
      <tr>
        <td><?= $ans['f_name']; ?> <?= $ans['l_name']; ?> (<?= $ans['code']; ?>)</td>
        <td><?= $ans['txt']; ?></td>
      </tr>
    <?php } ?>
    </tbody>
  </table>
<?php } ?>