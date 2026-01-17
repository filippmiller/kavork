<?php

namespace common\components;

use Yii;
use yii\base\BootstrapInterface;

/**
 * Security Headers Component
 *
 * Adds security-related HTTP headers to all responses:
 * - X-Content-Type-Options: nosniff
 * - X-Frame-Options: SAMEORIGIN
 * - X-XSS-Protection: 1; mode=block
 * - Referrer-Policy: strict-origin-when-cross-origin
 * - Permissions-Policy: geolocation=(), microphone=(), camera=()
 * - Content-Security-Policy (optional, can break some features)
 */
class SecurityHeaders implements BootstrapInterface
{
    /**
     * @var bool Enable Content-Security-Policy header
     */
    public $enableCSP = false;

    /**
     * @var string Content-Security-Policy value
     */
    public $cspPolicy = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://ajax.googleapis.com https://code.jquery.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'";

    /**
     * Bootstrap method
     */
    public function bootstrap($app)
    {
        $app->on(\yii\web\Application::EVENT_BEFORE_REQUEST, function () use ($app) {
            $this->setHeaders($app);
        });
    }

    /**
     * Set security headers on response
     */
    public function setHeaders($app)
    {
        if ($app instanceof \yii\console\Application) {
            return; // Skip for console applications
        }

        $response = $app->response;

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // XSS Protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Disable dangerous browser features
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');

        // Strict Transport Security (HTTPS only)
        if ($app->request->isSecureConnection) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content Security Policy (if enabled)
        if ($this->enableCSP) {
            $response->headers->set('Content-Security-Policy', $this->cspPolicy);
        }

        // Prevent IE from executing downloads in site's context
        $response->headers->set('X-Download-Options', 'noopen');

        // Cross-Origin policies
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
    }

    /**
     * Static method to add headers manually
     */
    public static function addHeaders()
    {
        $instance = new self();
        $instance->setHeaders(Yii::$app);
    }
}
