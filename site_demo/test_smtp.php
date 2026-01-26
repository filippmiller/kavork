<?php
/**
 * Gmail SMTP Test Script
 * Tests email sending with Gmail SMTP configuration
 *
 * Usage: php test_smtp.php
 */

// Load Composer autoloader and Yii
require(__DIR__ . '/vendor/autoload.php');
require(__DIR__ . '/vendor/yiisoft/yii2/Yii.php');

// Gmail SMTP Configuration
$smtpHost = 'smtp.gmail.com';
$smtpUsername = 'anticafe294@gmail.com';
$smtpPassword = 'qhrqcflmjhqnqlep'; // App Password (spaces removed)
$smtpPort = 587;
$encryption = 'tls';

// Test email details
$toEmail = 'anticafe294@gmail.com'; // Send test to same account
$fromEmail = 'anticafe294@gmail.com';
$fromName = 'Kavork Test System';
$subject = 'Kavork SMTP Test - ' . date('Y-m-d H:i:s');
$body = "✓ SUCCESS! Gmail SMTP is working correctly.\n\n";
$body .= "Configuration Details:\n";
$body .= "- SMTP Host: $smtpHost\n";
$body .= "- SMTP Port: $smtpPort\n";
$body .= "- Encryption: $encryption\n";
$body .= "- Sender: $fromEmail\n";
$body .= "\nTimestamp: " . date('Y-m-d H:i:s') . "\n";
$body .= "\nYour checkout receipts will be sent using this configuration.";

echo "=== Gmail SMTP Test ===\n";
echo "Host: $smtpHost\n";
echo "Username: $smtpUsername\n";
echo "Port: $smtpPort\n";
echo "Encryption: $encryption\n";
echo "To: $toEmail\n";
echo "\n";

try {
    echo "Creating SMTP transport...\n";

    // Create transport
    $transport = (new Swift_SmtpTransport($smtpHost, $smtpPort, $encryption))
        ->setUsername($smtpUsername)
        ->setPassword($smtpPassword);

    echo "Creating mailer...\n";

    // Create mailer
    $mailer = new Swift_Mailer($transport);

    echo "Creating message...\n";

    // Create message
    $message = (new Swift_Message($subject))
        ->setFrom([$fromEmail => $fromName])
        ->setTo([$toEmail])
        ->setBody($body);

    echo "Sending email...\n";

    // Send
    $result = $mailer->send($message);

    if ($result) {
        echo "\n";
        echo "════════════════════════════════════════\n";
        echo "✓ SUCCESS! Email sent successfully!\n";
        echo "════════════════════════════════════════\n";
        echo "\n";
        echo "Check your inbox at: $toEmail\n";
        echo "Subject: $subject\n";
        echo "\n";
        echo "Gmail SMTP is configured correctly!\n";
        echo "Ready to send checkout receipts.\n";
        exit(0);
    } else {
        echo "\n";
        echo "✗ FAILED! Email was not sent.\n";
        echo "No exception thrown, but send returned false.\n";
        exit(1);
    }

} catch (Swift_TransportException $e) {
    echo "\n";
    echo "════════════════════════════════════════\n";
    echo "✗ SMTP CONNECTION ERROR\n";
    echo "════════════════════════════════════════\n";
    echo "\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Possible causes:\n";
    echo "1. App Password is incorrect\n";
    echo "2. Gmail account has 2FA disabled\n";
    echo "3. Firewall blocking port 587\n";
    echo "4. Network connectivity issue\n";
    echo "\n";
    exit(1);

} catch (Exception $e) {
    echo "\n";
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
