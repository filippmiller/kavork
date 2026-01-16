<?php
use \frontend\modules\visits\models\VisitorLog;
  function str2csv($str){
    return '"'.str_replace('"','\\"',$str).'"';
  }
?>"Name","Data","Time","Pay type","Price (<?=$currency;?>)","VAT (<?=$currency;?>)","Cost(<?=$currency;?>)"
<?php
foreach ($result as $item){
  echo str2csv($controller->getUser($item['visitor_id'])).','.
      '"'.date(Yii::$app->params['lang']['date'],strtotime($item['finish'])).'",'.
      '"'.date(Yii::$app->params['lang']['time'],strtotime($item['finish'])).'",';
  echo '"'.Yii::t('app',$item['pay_state']==VisitorLog::PAY_METHOD_CARD?'card':'money').'",';
  echo '"'.number_format($item['sum'],2,'.','').'",';
  echo '"'.number_format($item['cost']-$item['sum'],2,'.',' ').'",';
  echo '"'.number_format($item['cost'],2,'.','').'"';
  echo "\n";
}
?>