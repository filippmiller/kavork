<?php

use yii\db\Migration;
use frontend\modules\templates\models\Template;
use frontend\modules\cafe\models\Cafe;

/**
 * Class m180905_135044_link_default_templates_to_cafes
 */
class m180905_135044_link_default_templates_to_cafes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	    $query = Cafe::find();
	    $query->orderBy('id');

	    $template_types = array_keys(Template::getTypeLabels());

	    foreach ($query->each() as $cafe) {
	    	foreach ($template_types as $template_type) {
			    $template = $cafe->getTemplate($template_type)->one();

			    if (!$template) {
				    $template = Template::findDefault($template_type);

				    if ($template && $template->scope_id == Template::SCOPE_DEFAULT) {
					    $cafe->link('templates', $template, ['type_id' => $template->type_id]);
				    }
			    }
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
