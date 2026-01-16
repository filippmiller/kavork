<?php

use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $searchModel frontend\modules\timetable\models\UserTimetableSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'User Timetables');
$this->params['breadcrumbs'][] = $this->title;

?>

<!--<div class="row  row-flex row-flex-wrap margin-top-10">-->
<div class="row  margin-top-10">
  <div class="col-md-2">
    <h6><?= Yii::t('timetable', 'Drag-and-drop Admins'); ?> </h6>
	<hr>
    <div id="external-events"></div>
    <div id="calendarTrash" class="calendar-trash"><img src="/img/trash.png"></div>
  </div>
  <div class="col-md-10">
    <?= edofre\fullcalendar\Fullcalendar::widget([
        'options' => [
            'id' => 'calendar',
            'language' => Yii::$app->params['lg_fullcalendar'][Yii::$app->language],
        ],
        'clientOptions' => [
            'editable' => Yii::$app->user->can('UserTimetableUpdate'),
            'droppable' => Yii::$app->user->can('UserTimetableUpdate'),
            'aspectRatio' => 2,
            'dragRevertDuration' => 1,
            'themeSystem' => 'bootstrap3',
            'timeFormat' => Yii::$app->params['lang']['time24Hour'] ? 'H(:mm)' : 'h(:mm)t',
            'smallTimeFormat' => Yii::$app->params['lang']['time24Hour'] ? 'H(:mm)' : 'h(:mm)t',
            'agenda'=> 'h:mm{ - h:mm}' ,
            'displayEventEnd' => true,
            'displayEventTime' => true,
            'allDaySlot' => false,
            'eventRender' => new JsExpression('tt_Render'),
            'eventDragStop' => new JsExpression('tt_eventDragStop'),
            'eventDrop' => new JsExpression('tt_updateEv'),
            'eventResize' => new JsExpression('tt_updateEv'),
            'eventReceive' => new JsExpression('tt_eventReceive'),
            'drop' => new JsExpression('tt_drop'),
            'firstDay' => Yii::$app->cafe->first_weekday,

        ],
        'header' => [
            'center' => 'title',
            'left' => 'prev,next today',
            'right' => 'month,agendaWeek,agendaDay,listWeek',
        ],
        'events' => Url::to(['jsoncalendar'])
    ]);
    ?>
  </div>
</div>

<?php Modal::begin([
    "id" => "ajaxCrudModal",
    "footer" => "",// always need it for jquery plugin
]) ?>
<?php Modal::end(); ?>
