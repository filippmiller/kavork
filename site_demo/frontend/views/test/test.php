<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 28.08.18
 * Time: 12:30
 */

use yii\web\View;

/* @var $this View */

?>

  TEST

  <div id="elfinder-test"></div>


<?php
$js = <<<JS

 var csrfParam = $('meta[name=csrf-param]').attr('content');
  var csrfToken = $('meta[name=csrf-token]').attr('content');

var customData = {};
  customData[csrfParam] = csrfToken;


var elf = $('#elfinder-test').elfinder({              
               
               url: '/elfinder/connect',  // connector URL
               customData: customData,
               dialog: {width: 900, modal: true, title: 'Select a file'},
               resizable: false,
               commandsOptions: {
                   getfile: {
                       oncomplete: 'destroy'
                   }
               },
               getFileCallback: function (file) {
                  console.log(file.path);
               }
           }).elfinder('instance');
JS;
$this->registerJs($js);

?>