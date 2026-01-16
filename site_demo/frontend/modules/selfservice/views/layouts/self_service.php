<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 17.10.18
 * Time: 21:50
 */

use frontend\modules\selfservice\assets\SelfServiceAsset;
use \yii\bootstrap\Modal;
use yii\helpers\Url;
SelfServiceAsset::register($this);

$cafe = Yii::$app->cafe;

?>

<?php $this->beginContent('@frontend/views/layouts/blank.twig'); ?>
<div id="ajaxCrudModal" class="fade modal" role="dialog" tabindex="-1">
		<div class="modal-dialog ">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>

				</div>
				<div class="modal-body">

				</div>
				<div class="modal-footer">

				</div>
			</div>
		</div>
	</div>

<!--<div class="row-flex row-flex-wrap">-->
<div class="header_self fg-white text-center">	
<div class="container">	
   <div class="col-sm-4 hidden-xs relative text-left" data-mh="my-group">
   <div class="icon_left">
      <a href="<?= Url::to(['/selfservice/default/dashboard']); ?>">
         <!--<button type="button" class="btn btn-science-blue btn-lg crut" style="text-transform: uppercase;"><i class="fa fa-arrow-circle-o-left"></i> initial screen </button>-->
         <h1 class="margin-off"><i class="icon-metro-arrow-left-3 fg-white"></i></h1>
      </a>
   </div>
   </div>
   <div class="col-sm-4 col-xs-12 text-center" data-mh="my-group">
      <h1 class="logos"><img src="<?= $cafe->getLogo(); ?>" alt="logo" class="logo"></h1>
      <h5 class="padding-off-bottom margin-top-10 hid_text fg-white"><?= $cafe->name; ?></h5>
   </div>
   <div class="col-sm-4 col-xs-12 relative text-right" data-mh="my-group">
      <!--<div class="lang_self">
         <div class="dropdown language">
            <button class="btn btn-science-blue dropdown-toggle" type="button" id="dropdownLang" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <img src="/img/flag/{{ language() }}.svg" alt="{{ language() }}">
            {{ params('lg_list')[language()] }}
    <i class="fa fa-angle-down"></i>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownLang">
               {% for code,name in lg_list() %}
               <li><a {{ make_lang_url(code) | raw }}><img src="/img/flag/{{ code }}.svg" alt=""> {{ name }}</a></li>
               {% endfor %}
            </ul>
         </div>
      </div>-->

      <div class="lang_self">
         <div class="dropdown language">
		  {% if lg_list()|length == 2 %}
               {% for code,name in lg_list() %}
			<a {{ make_lang_url(code) | raw }} style="color:#ffffff; text-decoration:none !important;">  
            <button class="btn btn-science-blue" type="button" style="display:inline-block" id="dropdownLang" >
			   <img src="/img/flag/{{ code }}.svg" alt=""> {{ name }}
            </button>
			</a>
               {% endfor %}
			   {% endif %}
			   {% if lg_list()|length > 2 %}
			    <button class="btn btn-science-blue dropdown-toggle" type="button" id="dropdownLang" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <img src="/img/flag/{{ language() }}.svg" alt="{{ language() }}">
            {{ params('lg_list')[language()] }}
    <i class="fa fa-angle-down"></i>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownLang">
               {% for code,name in lg_list() %}
               <li><a {{ make_lang_url(code) | raw }}><img src="/img/flag/{{ code }}.svg" alt=""> {{ name }}</a></li>
               {% endfor %}
            </ul>
			{% endif %}
         </div>
      </div>
   </div>
</div>
</div>

	
<div class="content_self fg-white">	
<div class="container">				    		
		<div class="vert_centr">
			<?= $content ?>		
		</div>
</div>
</div>		
<?php $this->endContent(); ?>

<?php
Modal::begin([
    'id'=>'secondModal',
    'header' => '',
    'size' => 'modal-lg',
]);
Modal::end();
?>
