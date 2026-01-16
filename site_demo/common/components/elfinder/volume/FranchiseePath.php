<?php
/**
 * Created by PhpStorm.
 * User: acid
 * Date: 28.08.18
 * Time: 17:03
 */

namespace common\components\elfinder\volume;

use mihaildev\elfinder\volume\Local;
use yii\helpers\FileHelper;
use Yii;

class FranchiseePath extends Local
{
	public function isAvailable()
	{
		if (Yii::$app->user->isGuest) {
			return false;
		}

		if (!isset(Yii::$app->cafe)) {
			return false;
		}

		if (Yii::$app->cafe->getFranchiseeId() === null) {
			return false;
		}

		return parent::isAvailable();
	}

	public function getUrl()
	{
		$path = strtr($this->path, ['{id}' => Yii::$app->cafe->getFranchiseeId()]);
		return Yii::getAlias($this->baseUrl . '/' . trim($path, '/'));
	}

	public function getRealPath()
	{
		$path = strtr($this->path, ['{id}' => Yii::$app->cafe->getFranchiseeId()]);
		$path = Yii::getAlias($this->basePath . '/' . trim($path, '/'));

		if (!is_dir($path)) {
			FileHelper::createDirectory($path, 0777, true);
		}

		return $path;
	}
}