<?php

use frontend\modules\templates\models\Template;
use yii\db\Migration;

/**
 * Class m180905_135044_link_default_templates_to_cafes
 */
class m180905_135043_add_default_templates extends Migration
{
  public $tpl = '[{"type":"logo","data":{"cafe_logo":"/img/logo_black.png","align":"center","height":"100","background":"#ffffff","padding_v":"20","padding_h":"40"}},{"type":"driver","data":{"cafe_logo":"/img/logo_black.png","color":"#c0c0c0","width":"1","background":"#ffffff","padding_v":"20","padding_h":"40"}},{"type":"text","data":{"cafe_logo":"/img/logo_black.png","html":{"en-EN":"<p>{ cafe.name }</p>\n\n<p>{ visitor.l_name }</p>\n\n<p>{ visitor.f_name }</p>\n","fr":"<p>fr sample text</p>\n","ru-RU":"<p>ru-RU sample text</p>\n"},"background":"#ffffff","padding_v":"10","padding_h":"15"}},{"type":"table","data":{"cafe_logo":"/img/logo_black.png","direction":"horizontal","items":["counter","add_time","finish_time","duration","pause","cost","sum","vat","vat_total","guest_m","guest_chi","certificate"],"align":"center","background":"#ffffff","render":"table","label":"display","padding_v":"20","padding_h":"40","hidden":["finish_time","duration","guest_chi","guest_m"],"table_list":["counter","add_time","finish_time","duration","pause","cost","sum","vat","vat_total","guest_m","guest_chi","certificate"]},"sub_type":"visits"},{"type":"table","data":{"cafe_logo":"/img/logo_black.png","direction":"vertical","items":["counter","add_time","finish_time","duration","pause","cost","sum","vat","vat_total","guest_m","guest_chi","certificate"],"align":"center","background":"#ffffff","render":"total","label":"display","padding_v":"20","padding_h":"40","hidden":["add_time","finish_time","duration","pause"],"table_list":["counter","add_time","finish_time","duration","pause","cost","sum","vat","vat_total","guest_m","guest_chi","certificate"]},"sub_type":"visits"},{"type":"table","data":{"cafe_logo":"/img/logo_black.png","direction":"horizontal","items":["counter","name","sum","vat","count","total"],"align":"center","background":"#ffffff","render":"all","label":"display","padding_v":"20","padding_h":"40","hidden":[]},"sub_type":"cart"},{"type":"table","data":{"cafe_logo":"/img/logo_black.png","direction":"vertical","items":["cost","vat","vat_total","sum"],"align":"center","background":"#ffffff","render":"total","label":"display","padding_v":"20","padding_h":"40","hidden":[]},"sub_type":"total"}]';

  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $query = Template::find();
    $query->andWhere(['scope_id' => Template::SCOPE_DEFAULT]);

    $template_types = array_keys(Template::getTypeLabels());

    foreach ($template_types as $type) {
      $query->andWhere(['type_id' => $type]);
      if (!$query->exists()) {
        $template = new Template();
        $template->setAttributes([
            'scope_id' => Template::SCOPE_DEFAULT,
            'type_id' => $type,
        ]);
        $template->content = $this->tpl;
        $template->save(false);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    return true;
  }
}
