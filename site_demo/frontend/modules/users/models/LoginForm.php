<?php

namespace frontend\modules\users\models;

use Yii;
use yii\base\Model;
use common\models\SecurityLog;

/**
 * Login form with rate limiting
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    /**
     * @var string Rate limit error message
     */
    public $rateLimitError;

    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // Check rate limit before password validation
            ['username', 'validateRateLimit'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'User'),
            'password' => Yii::t('app', 'Password'),
            'rememberMe' => Yii::t('app', 'remember me'),
        ];
    }

    /**
     * Validates rate limit before allowing login attempt
     */
    public function validateRateLimit($attribute, $params)
    {
        if ($this->hasErrors()) {
            return;
        }

        // Check if rate limiter component exists
        if (!Yii::$app->has('loginRateLimiter')) {
            return; // Skip if component not configured
        }

        try {
            $rateLimiter = Yii::$app->loginRateLimiter;
            $result = $rateLimiter->checkLoginAllowed($this->username);

            if (!$result['allowed']) {
                $this->rateLimitError = $result['message'];
                $this->addError($attribute, $result['message']);
            }
        } catch (\Exception $e) {
            // Log error but don't block login if rate limiter fails
            Yii::error("Rate limiter error: " . $e->getMessage());
        }
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password.'));

                // Record failed attempt
                $this->recordLoginAttempt(false);
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();

            // Record successful login
            $this->recordLoginAttempt(true);

            // Log successful login
            SecurityLog::logLoginSuccess($user->id, $this->username);

            return Yii::$app->user->login($user, $this->rememberMe ? 3600 * 12 : 0);
        }

        return false;
    }

    /**
     * Record login attempt for rate limiting
     */
    protected function recordLoginAttempt($success)
    {
        if (!Yii::$app->has('loginRateLimiter')) {
            return;
        }

        try {
            Yii::$app->loginRateLimiter->recordAttempt($this->username, $success);
        } catch (\Exception $e) {
            Yii::error("Failed to record login attempt: " . $e->getMessage());
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Users::findByuser($this->username);
        }

        return $this->_user;
    }

    /**
     * Get remaining login attempts
     *
     * @return int|null
     */
    public function getRemainingAttempts()
    {
        if (!Yii::$app->has('loginRateLimiter')) {
            return null;
        }

        try {
            $rateLimiter = Yii::$app->loginRateLimiter;
            $result = $rateLimiter->checkLoginAllowed($this->username);

            if ($result['allowed']) {
                return $rateLimiter->maxAttempts - $this->getFailedAttempts();
            }

            return 0;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get current failed attempts count
     */
    protected function getFailedAttempts()
    {
        try {
            $ip = SecurityLog::getClientIp();
            return SecurityLog::getRecentFailedLoginsByIp($ip, 15);
        } catch (\Exception $e) {
            return 0;
        }
    }
}
