<?php

namespace frontend\components;

use frontend\modules\constants\models\Constants;
use Yii;
use yii\web\UrlRuleInterface;

//use common\models\GeoIpCountry;
//use frontend\modules\country\models\CountryToLanguage;

class UrlLocalisation implements UrlRuleInterface
{
  private $params;

  ///private $url_pref='/';
  public function parseRequest($manager, $request)
  {
    $lg = explode('/', $request->pathInfo)[0];
    $url = $request->url;
    $pathInfo = $request->pathInfo;

    if (isset(Yii::$app->params['lg_list'][$lg])) {
      $url = preg_replace("/^\/$lg/", '', $url);
      $url = '/' . trim($url, '/');
      //$request->baseUrl = '/' . $lg;
      //$request->url = $url;
      $pathInfo = '/' . explode('?', trim($url, '/'))[0];
      $pathInfo = str_replace('//', '/', $pathInfo);

      Yii::$app->session->set('lg', $lg);
      $get = $request->get();
      if(count($get)>0){
        $pathInfo.='?'.http_build_query($get);
      }
      //ddd($pathInfo,$request->pathInfo,$request->get());
      Yii::$app->response->redirect($pathInfo, 301)->send();
      exit();
    } else {
      $lg = Yii::$app->session->get('lg',
          !Yii::$app->user->isGuest ?
              Yii::$app->user->identity->lg :
              array_keys(Yii::$app->cafe->languageList)[0]
      );

      if (!isset(Yii::$app->cafe->languageList[$lg])) {
        $lg = array_keys(Yii::$app->cafe->languageList)[0];
      }
    }

    //if(!Yii::$app->user->isGuest) {
    Yii::$app->language = $lg;
    Yii::$app->params['lg_shot'] = strtolower((explode('-', Yii::$app->language)[0]));
    /*}else{
      Yii::$app->session->remove('lg');
    }*/
    //ddd($lg);

    //Применени некоторых языковых параметров
    Yii::$app->params += require __DIR__ . '/../config/params_with_i18n.php';

    return false;
  }

  public function createUrl($route, $params = array(), $ampersand = '&')
  {
    return false;
  }
}
