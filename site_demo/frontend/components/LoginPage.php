<?php

namespace frontend\components;

use Yii;
use yii\web\UrlRuleInterface;


class LoginPage implements UrlRuleInterface
{
  /**
   * Parses the given request and returns the corresponding route and parameters.
   * @param \yii\web\UrlManager $manager the URL manager
   * @param \yii\web\Request $request the request component
   * @return array|boolean the parsing result. The route and the parameters are returned as an array.
   * If false, it means this rule cannot be used to parse this path info.
   */
  public function parseRequest($manager, $request)
  {

    if (!Yii::$app->user->isGuest) {
    	// Cafe not Setted - Going to Set
      if (!Yii::$app->session->get('cafe_id', false)) {
        return ["site/change-cafe", []];
      }

      $selfServiceModeKey = \frontend\modules\selfservice\Module::SESSION_MODE_KEY;
	    if (
		    !Yii::$app->session->get($selfServiceModeKey, false) &&
		    $mode = Yii::$app->request->cookies->getValue($selfServiceModeKey, false)
	    ) {
		    // We have Self-service cookie - Going to Self-service dashboard
		    Yii::$app->session->set($selfServiceModeKey, $mode);
		    return ["selfservice/default/dashboard", []];
	    }

      return false;
    }
    if (empty($manager->baseUrl)) return false;
    return ["site/login", []];
  }


  /**
   * Creates a URL according to the given route and parameters.
   * @param \yii\web\UrlManager $manager the URL manager
   * @param string $route the route. It should not have slashes at the beginning or the end.
   * @param array $params the parameters
   * @return string|boolean the created URL, or false if this rule cannot be used for creating this URL.
   */
  public function createUrl($manager, $route, $params)
  {
    return false;
  }

}