<?php

namespace common\components;

use Yii;
use yii\base\Component;
use common\models\SecurityLog;

/**
 * Login Rate Limiter with Exponential Backoff
 *
 * Implements progressive delays on failed login attempts:
 * - 1-3 attempts: No delay
 * - 4-5 attempts: 30 second delay
 * - 6-7 attempts: 2 minute delay
 * - 8-9 attempts: 5 minute delay
 * - 10+ attempts: Account locked for 30 minutes
 */
class LoginRateLimiter extends Component
{
    /**
     * @var int Maximum attempts before lockout
     */
    public $maxAttempts = 10;

    /**
     * @var int Lockout duration in seconds (30 minutes)
     */
    public $lockoutDuration = 1800;

    /**
     * @var int Time window for counting attempts (15 minutes)
     */
    public $attemptWindow = 900;

    /**
     * @var array Delay thresholds [attempts => delay_seconds]
     */
    public $delayThresholds = [
        4 => 30,      // 30 seconds after 4 attempts
        6 => 120,     // 2 minutes after 6 attempts
        8 => 300,     // 5 minutes after 8 attempts
    ];

    /**
     * Check if login is allowed for given username/IP
     *
     * @param string $username
     * @return array ['allowed' => bool, 'wait_seconds' => int, 'reason' => string]
     */
    public function checkLoginAllowed($username)
    {
        $ip = SecurityLog::getClientIp();

        // Check IP-based rate limit
        $ipAttempts = $this->getRecentAttempts(null, $ip);
        if ($ipAttempts >= $this->maxAttempts) {
            $waitTime = $this->getWaitTime(null, $ip);
            if ($waitTime > 0) {
                SecurityLog::logLoginBlocked($username, "IP blocked: too many attempts");
                return [
                    'allowed' => false,
                    'wait_seconds' => $waitTime,
                    'reason' => 'too_many_attempts_ip',
                    'message' => Yii::t('app', 'Too many login attempts from this IP. Please wait {minutes} minutes.', [
                        'minutes' => ceil($waitTime / 60)
                    ])
                ];
            }
        }

        // Check username-based rate limit
        $userAttempts = $this->getRecentAttempts($username, null);
        if ($userAttempts >= $this->maxAttempts) {
            $waitTime = $this->getWaitTime($username, null);
            if ($waitTime > 0) {
                SecurityLog::logLoginBlocked($username, "Account locked: too many attempts");
                return [
                    'allowed' => false,
                    'wait_seconds' => $waitTime,
                    'reason' => 'account_locked',
                    'message' => Yii::t('app', 'Account temporarily locked. Please wait {minutes} minutes.', [
                        'minutes' => ceil($waitTime / 60)
                    ])
                ];
            }
        }

        // Check if delay is required (exponential backoff)
        $attempts = max($ipAttempts, $userAttempts);
        $delay = $this->getRequiredDelay($attempts);

        if ($delay > 0) {
            $lastAttempt = $this->getLastAttemptTime($username, $ip);
            $elapsed = time() - $lastAttempt;

            if ($elapsed < $delay) {
                $waitTime = $delay - $elapsed;
                return [
                    'allowed' => false,
                    'wait_seconds' => $waitTime,
                    'reason' => 'rate_limited',
                    'message' => Yii::t('app', 'Please wait {seconds} seconds before trying again.', [
                        'seconds' => $waitTime
                    ])
                ];
            }
        }

        return [
            'allowed' => true,
            'wait_seconds' => 0,
            'reason' => null,
            'message' => null
        ];
    }

    /**
     * Record a login attempt
     *
     * @param string $username
     * @param bool $success
     */
    public function recordAttempt($username, $success)
    {
        $ip = SecurityLog::getClientIp();

        // Use database to track attempts
        $db = Yii::$app->db;

        try {
            $db->createCommand()->insert('{{%login_attempts}}', [
                'username' => $username,
                'ip_address' => $ip,
                'attempted_at' => time(),
                'success' => $success ? 1 : 0,
            ])->execute();
        } catch (\Exception $e) {
            // Table might not exist yet, log error but don't break login
            Yii::error("Failed to record login attempt: " . $e->getMessage());
        }

        // Log security event
        if ($success) {
            SecurityLog::logLoginSuccess(null, $username);
            $this->clearAttempts($username, $ip);
        } else {
            SecurityLog::logLoginFailed($username);

            // Check if account should be locked
            $attempts = $this->getRecentAttempts($username, null);
            if ($attempts >= $this->maxAttempts) {
                SecurityLog::logAccountLocked(null, $username, $this->lockoutDuration / 60);
            }
        }
    }

    /**
     * Get recent failed attempts count
     */
    protected function getRecentAttempts($username = null, $ip = null)
    {
        $since = time() - $this->attemptWindow;

        try {
            $query = (new \yii\db\Query())
                ->from('{{%login_attempts}}')
                ->where(['success' => 0])
                ->andWhere(['>=', 'attempted_at', $since]);

            if ($username) {
                $query->andWhere(['username' => $username]);
            }
            if ($ip) {
                $query->andWhere(['ip_address' => $ip]);
            }

            return (int)$query->count();
        } catch (\Exception $e) {
            return 0; // Table might not exist
        }
    }

    /**
     * Get wait time remaining
     */
    protected function getWaitTime($username = null, $ip = null)
    {
        $lastAttempt = $this->getLastAttemptTime($username, $ip);
        if (!$lastAttempt) {
            return 0;
        }

        $elapsed = time() - $lastAttempt;
        $remaining = $this->lockoutDuration - $elapsed;

        return max(0, $remaining);
    }

    /**
     * Get last attempt time
     */
    protected function getLastAttemptTime($username = null, $ip = null)
    {
        try {
            $query = (new \yii\db\Query())
                ->select('attempted_at')
                ->from('{{%login_attempts}}')
                ->where(['success' => 0])
                ->orderBy(['attempted_at' => SORT_DESC])
                ->limit(1);

            if ($username) {
                $query->andWhere(['username' => $username]);
            }
            if ($ip) {
                $query->andWhere(['ip_address' => $ip]);
            }

            return (int)$query->scalar();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get required delay based on attempt count
     */
    protected function getRequiredDelay($attempts)
    {
        $delay = 0;
        foreach ($this->delayThresholds as $threshold => $seconds) {
            if ($attempts >= $threshold) {
                $delay = $seconds;
            }
        }
        return $delay;
    }

    /**
     * Clear attempts after successful login
     */
    protected function clearAttempts($username, $ip)
    {
        try {
            Yii::$app->db->createCommand()
                ->delete('{{%login_attempts}}', [
                    'or',
                    ['username' => $username],
                    ['ip_address' => $ip]
                ])
                ->execute();
        } catch (\Exception $e) {
            // Ignore errors
        }
    }

    /**
     * Clean old attempts (run periodically)
     */
    public function cleanOldAttempts()
    {
        $threshold = time() - ($this->attemptWindow * 2);

        try {
            Yii::$app->db->createCommand()
                ->delete('{{%login_attempts}}', ['<', 'attempted_at', $threshold])
                ->execute();
        } catch (\Exception $e) {
            // Ignore errors
        }
    }
}
