<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * Security event logging model
 *
 * @property int $id
 * @property string $event_type
 * @property int|null $user_id
 * @property string|null $username
 * @property string $ip_address
 * @property string|null $user_agent
 * @property string|null $details
 * @property int $created_at
 */
class SecurityLog extends ActiveRecord
{
    // Event types
    const EVENT_LOGIN_SUCCESS = 'login_success';
    const EVENT_LOGIN_FAILED = 'login_failed';
    const EVENT_LOGIN_BLOCKED = 'login_blocked';
    const EVENT_LOGOUT = 'logout';
    const EVENT_PASSWORD_CHANGE = 'password_change';
    const EVENT_PASSWORD_RESET_REQUEST = 'password_reset_request';
    const EVENT_ACCOUNT_LOCKED = 'account_locked';
    const EVENT_ACCOUNT_UNLOCKED = 'account_unlocked';
    const EVENT_RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
    const EVENT_SUSPICIOUS_ACTIVITY = 'suspicious_activity';

    public static function tableName()
    {
        return '{{%security_log}}';
    }

    public function rules()
    {
        return [
            [['event_type', 'ip_address', 'created_at'], 'required'],
            [['user_id', 'created_at'], 'integer'],
            [['details'], 'string'],
            [['event_type'], 'string', 'max' => 50],
            [['username'], 'string', 'max' => 255],
            [['ip_address'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 500],
        ];
    }

    /**
     * Log a security event
     */
    public static function log($eventType, $userId = null, $username = null, $details = null)
    {
        $log = new self();
        $log->event_type = $eventType;
        $log->user_id = $userId;
        $log->username = $username;
        $log->ip_address = self::getClientIp();
        $log->user_agent = self::getUserAgent();
        $log->details = $details ? json_encode($details) : null;
        $log->created_at = time();

        return $log->save();
    }

    /**
     * Log successful login
     */
    public static function logLoginSuccess($userId, $username)
    {
        return self::log(self::EVENT_LOGIN_SUCCESS, $userId, $username);
    }

    /**
     * Log failed login attempt
     */
    public static function logLoginFailed($username, $reason = null)
    {
        return self::log(self::EVENT_LOGIN_FAILED, null, $username, ['reason' => $reason]);
    }

    /**
     * Log blocked login attempt
     */
    public static function logLoginBlocked($username, $reason)
    {
        return self::log(self::EVENT_LOGIN_BLOCKED, null, $username, ['reason' => $reason]);
    }

    /**
     * Log logout
     */
    public static function logLogout($userId, $username)
    {
        return self::log(self::EVENT_LOGOUT, $userId, $username);
    }

    /**
     * Log account locked
     */
    public static function logAccountLocked($userId, $username, $duration)
    {
        return self::log(self::EVENT_ACCOUNT_LOCKED, $userId, $username, ['duration_minutes' => $duration]);
    }

    /**
     * Log rate limit exceeded
     */
    public static function logRateLimitExceeded($identifier, $endpoint)
    {
        return self::log(self::EVENT_RATE_LIMIT_EXCEEDED, null, null, [
            'identifier' => $identifier,
            'endpoint' => $endpoint
        ]);
    }

    /**
     * Get client IP address
     */
    public static function getClientIp()
    {
        $request = Yii::$app->request;

        // Check for proxy headers (Railway uses proxies)
        $headers = ['X-Forwarded-For', 'X-Real-IP', 'CF-Connecting-IP'];
        foreach ($headers as $header) {
            $value = $request->headers->get($header);
            if ($value) {
                // X-Forwarded-For may contain multiple IPs, get the first one
                $ips = explode(',', $value);
                return trim($ips[0]);
            }
        }

        return $request->userIP ?? '0.0.0.0';
    }

    /**
     * Get user agent
     */
    public static function getUserAgent()
    {
        $ua = Yii::$app->request->userAgent;
        return $ua ? substr($ua, 0, 500) : null;
    }

    /**
     * Clean old logs (retention: 90 days)
     */
    public static function cleanOldLogs($days = 90)
    {
        $threshold = time() - ($days * 86400);
        return self::deleteAll(['<', 'created_at', $threshold]);
    }

    /**
     * Get recent failed logins for IP
     */
    public static function getRecentFailedLoginsByIp($ip, $minutes = 15)
    {
        $since = time() - ($minutes * 60);
        return self::find()
            ->where(['event_type' => self::EVENT_LOGIN_FAILED])
            ->andWhere(['ip_address' => $ip])
            ->andWhere(['>=', 'created_at', $since])
            ->count();
    }

    /**
     * Get recent failed logins for username
     */
    public static function getRecentFailedLoginsByUsername($username, $minutes = 15)
    {
        $since = time() - ($minutes * 60);
        return self::find()
            ->where(['event_type' => self::EVENT_LOGIN_FAILED])
            ->andWhere(['username' => $username])
            ->andWhere(['>=', 'created_at', $since])
            ->count();
    }
}
