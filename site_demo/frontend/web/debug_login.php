<?php
/**
 * Debug script to test login flow and capture errors
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include Yii bootstrap
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

echo "<pre>\n";
echo "=== Debug Login Test ===\n\n";

try {
    $app = new yii\web\Application($config);

    // Test finding user
    echo "1. Looking for user 'filipp1'...\n";
    $user = frontend\modules\users\models\Users::findByuser('filipp1');

    if ($user) {
        echo "   Found user: ID={$user->id}, Name={$user->name}, Franchisee={$user->franchisee_id}\n";

        // Check user roles
        echo "\n2. Checking user roles...\n";
        $roles = $user->getRoleOfUserArray();
        echo "   Roles: " . print_r($roles, true) . "\n";

        // Check user cafes
        echo "\n3. Checking user cafes...\n";
        $cafes = frontend\modules\users\models\Users::getCafesList();
        echo "   Cafes available: " . count($cafes) . "\n";
        if ($cafes) {
            foreach ($cafes as $cafe) {
                echo "   - Cafe ID={$cafe['id']}, Name={$cafe['name']}\n";
            }
        }

        // Try to simulate login
        echo "\n4. Simulating login...\n";
        $loginResult = Yii::$app->user->login($user);
        echo "   Login result: " . ($loginResult ? 'SUCCESS' : 'FAILED') . "\n";

        if ($loginResult) {
            // Check what goHome() would do
            echo "\n5. Checking home URL...\n";
            echo "   Home URL: " . Yii::$app->homeUrl . "\n";

            // Check if cafe component is working
            echo "\n6. Checking cafe component...\n";
            echo "   Cafe ID: " . (Yii::$app->cafe->id ?? 'not set') . "\n";
            echo "   Cafe Name: " . (Yii::$app->cafe->name ?? 'not set') . "\n";
        }
    } else {
        echo "   ERROR: User not found!\n";
    }
} catch (Exception $e) {
    echo "\n!!! EXCEPTION !!!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "\n!!! ERROR !!!\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Done ===\n";
echo "</pre>";
