<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\web\TooManyRequestsHttpException;
use common\models\SecurityLog;

/**
 * API Rate Limiter
 *
 * Implements per-IP and per-user rate limits:
 * - Login attempts: 5/minute per IP
 * - Authenticated API: 100/minute per user
 * - Unauthenticated API: 30/minute per IP
 * - Password reset: 3/hour per email
 */
class ApiRateLimiter extends Component
{
    /**
     * @var array Rate limits by endpoint type [type => [limit, window_seconds]]
     */
    public $limits = [
        'login' => [5, 60],           // 5 per minute
        'api_auth' => [100, 60],      // 100 per minute for authenticated
        'api_guest' => [30, 60],      // 30 per minute for guests
        'password_reset' => [3, 3600], // 3 per hour
        'default' => [60, 60],        // 60 per minute default
    ];

    /**
     * Check rate limit for endpoint
     *
     * @param string $endpoint Endpoint type (login, api_auth, api_guest, password_reset)
     * @param string|null $identifier User ID or IP
     * @return bool True if request is allowed
     * @throws TooManyRequestsHttpException
     */
    public function check($endpoint = 'default', $identifier = null)
    {
        if (!$identifier) {
            $identifier = $this->getIdentifier();
        }

        $limit = $this->limits[$endpoint] ?? $this->limits['default'];
        list($maxRequests, $windowSeconds) = $limit;

        $currentCount = $this->getCurrentCount($identifier, $endpoint, $windowSeconds);

        // Set rate limit headers
        $this->setRateLimitHeaders($maxRequests, $currentCount, $windowSeconds);

        if ($currentCount >= $maxRequests) {
            SecurityLog::logRateLimitExceeded($identifier, $endpoint);

            throw new TooManyRequestsHttpException(
                Yii::t('app', 'Rate limit exceeded. Please wait before making more requests.')
            );
        }

        // Increment counter
        $this->incrementCounter($identifier, $endpoint, $windowSeconds);

        return true;
    }

    /**
     * Check without throwing exception
     *
     * @return array ['allowed' => bool, 'remaining' => int, 'reset' => int]
     */
    public function checkSoft($endpoint = 'default', $identifier = null)
    {
        if (!$identifier) {
            $identifier = $this->getIdentifier();
        }

        $limit = $this->limits[$endpoint] ?? $this->limits['default'];
        list($maxRequests, $windowSeconds) = $limit;

        $currentCount = $this->getCurrentCount($identifier, $endpoint, $windowSeconds);
        $remaining = max(0, $maxRequests - $currentCount);
        $windowStart = $this->getWindowStart($windowSeconds);
        $reset = $windowStart + $windowSeconds;

        return [
            'allowed' => $currentCount < $maxRequests,
            'remaining' => $remaining,
            'limit' => $maxRequests,
            'reset' => $reset,
        ];
    }

    /**
     * Get identifier (user ID or IP)
     */
    protected function getIdentifier()
    {
        // Use user ID if authenticated, otherwise use IP
        if (!Yii::$app->user->isGuest) {
            return 'user_' . Yii::$app->user->id;
        }

        return 'ip_' . SecurityLog::getClientIp();
    }

    /**
     * Get current request count
     */
    protected function getCurrentCount($identifier, $endpoint, $windowSeconds)
    {
        $windowStart = $this->getWindowStart($windowSeconds);

        try {
            $count = (new \yii\db\Query())
                ->select('requests')
                ->from('{{%rate_limit}}')
                ->where([
                    'identifier' => $identifier,
                    'endpoint' => $endpoint,
                    'window_start' => $windowStart,
                ])
                ->scalar();

            return (int)$count;
        } catch (\Exception $e) {
            // Table might not exist, allow request
            return 0;
        }
    }

    /**
     * Increment request counter
     */
    protected function incrementCounter($identifier, $endpoint, $windowSeconds)
    {
        $windowStart = $this->getWindowStart($windowSeconds);

        try {
            $db = Yii::$app->db;

            // Try to update existing record
            $affected = $db->createCommand()
                ->update('{{%rate_limit}}',
                    ['requests' => new \yii\db\Expression('requests + 1')],
                    [
                        'identifier' => $identifier,
                        'endpoint' => $endpoint,
                        'window_start' => $windowStart,
                    ]
                )
                ->execute();

            // If no record exists, create one
            if ($affected === 0) {
                $db->createCommand()->insert('{{%rate_limit}}', [
                    'identifier' => $identifier,
                    'endpoint' => $endpoint,
                    'requests' => 1,
                    'window_start' => $windowStart,
                ])->execute();
            }
        } catch (\Exception $e) {
            // Ignore errors - don't break functionality if rate limit table doesn't exist
            Yii::error("Rate limit increment failed: " . $e->getMessage());
        }
    }

    /**
     * Get window start timestamp
     */
    protected function getWindowStart($windowSeconds)
    {
        return floor(time() / $windowSeconds) * $windowSeconds;
    }

    /**
     * Set rate limit headers
     */
    protected function setRateLimitHeaders($limit, $current, $window)
    {
        $response = Yii::$app->response;
        $remaining = max(0, $limit - $current);
        $reset = $this->getWindowStart($window) + $window;

        $response->headers->set('X-RateLimit-Limit', $limit);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', $reset);
    }

    /**
     * Clean old rate limit records (run periodically)
     */
    public function cleanOldRecords()
    {
        $threshold = time() - 7200; // Keep 2 hours of data

        try {
            Yii::$app->db->createCommand()
                ->delete('{{%rate_limit}}', ['<', 'window_start', $threshold])
                ->execute();
        } catch (\Exception $e) {
            // Ignore errors
        }
    }
}
