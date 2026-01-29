<?php

namespace frontend\components;

use Yii;

class WebUser extends \yii\web\User
{
    public function switchIdentity($identity, $duration = 0)
    {
        $this->setIdentity($identity);

        if (!$this->enableSession) {
            return;
        }

        if ($this->enableAutoLogin && ($this->autoRenewCookie || $identity === null)) {
            $this->removeIdentityCookie();
        }

        $session = Yii::$app->getSession();
        if (!YII_ENV_TEST) {
            if (!(YII_ENV_DEV && in_array(Yii::$app->request->userIP, ['127.0.0.1', '::1'], true))) {
                $session->regenerateID(true);
            }
        }
        $session->remove($this->idParam);
        $session->remove($this->authTimeoutParam);

        if ($identity) {
            $session->set($this->idParam, $identity->getId());
            if ($this->authTimeout !== null) {
                $session->set($this->authTimeoutParam, time() + $this->authTimeout);
            }
            if ($this->absoluteAuthTimeout !== null) {
                $session->set($this->absoluteAuthTimeoutParam, time() + $this->absoluteAuthTimeout);
            }
            if ($this->enableAutoLogin && $duration > 0) {
                $this->sendIdentityCookie($identity, $duration);
            }
        }
    }
}
