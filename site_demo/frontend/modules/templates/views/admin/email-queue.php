<?php
use yii\helpers\Html;

/* @var $stats array */
$stats = $stats ?: [];
$total = (int)($stats['total'] ?? 0);
$pending = (int)($stats['pending'] ?? 0);
$processing = (int)($stats['processing'] ?? 0);
$done = (int)($stats['done'] ?? 0);
?>

<?= Html::tag('p', 'Queue counts include all background jobs. "Done" means the job executed without an exception.') ?>
<?= Html::tag('ul',
    Html::tag('li', 'Total: ' . $total) .
    Html::tag('li', 'Pending: ' . $pending) .
    Html::tag('li', 'Processing: ' . $processing) .
    Html::tag('li', 'Done: ' . $done)
) ?>
