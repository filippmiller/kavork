<?php

use yii\db\Migration;

/**
 * Security enhancement migration
 * - login_attempts table for rate limiting
 * - security_log table for audit trail
 * - rate_limit table for API rate limiting
 */
class m260117_200000_security_tables extends Migration
{
    public function safeUp()
    {
        // Login attempts tracking for rate limiting
        $this->createTable('{{%login_attempts}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull(),
            'ip_address' => $this->string(45)->notNull(), // IPv6 support
            'attempted_at' => $this->integer()->notNull(),
            'success' => $this->boolean()->defaultValue(false),
        ]);

        $this->createIndex('idx_login_attempts_username', '{{%login_attempts}}', 'username');
        $this->createIndex('idx_login_attempts_ip', '{{%login_attempts}}', 'ip_address');
        $this->createIndex('idx_login_attempts_time', '{{%login_attempts}}', 'attempted_at');

        // Security event log for audit trail
        $this->createTable('{{%security_log}}', [
            'id' => $this->primaryKey(),
            'event_type' => $this->string(50)->notNull(), // login_success, login_failed, logout, password_change, etc.
            'user_id' => $this->integer()->null(),
            'username' => $this->string(255)->null(),
            'ip_address' => $this->string(45)->notNull(),
            'user_agent' => $this->string(500)->null(),
            'details' => $this->text()->null(), // JSON encoded extra details
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_security_log_event', '{{%security_log}}', 'event_type');
        $this->createIndex('idx_security_log_user', '{{%security_log}}', 'user_id');
        $this->createIndex('idx_security_log_ip', '{{%security_log}}', 'ip_address');
        $this->createIndex('idx_security_log_time', '{{%security_log}}', 'created_at');

        // API rate limiting table
        $this->createTable('{{%rate_limit}}', [
            'id' => $this->primaryKey(),
            'identifier' => $this->string(255)->notNull(), // IP or user_id
            'endpoint' => $this->string(100)->notNull(), // endpoint category
            'requests' => $this->integer()->defaultValue(0),
            'window_start' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_rate_limit_identifier', '{{%rate_limit}}', ['identifier', 'endpoint']);
        $this->createIndex('idx_rate_limit_window', '{{%rate_limit}}', 'window_start');

        // Add lockout columns to user table
        $this->addColumn('{{%user}}', 'failed_login_attempts', $this->integer()->defaultValue(0));
        $this->addColumn('{{%user}}', 'locked_until', $this->integer()->null());
        $this->addColumn('{{%user}}', 'last_failed_login', $this->integer()->null());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'failed_login_attempts');
        $this->dropColumn('{{%user}}', 'locked_until');
        $this->dropColumn('{{%user}}', 'last_failed_login');

        $this->dropTable('{{%rate_limit}}');
        $this->dropTable('{{%security_log}}');
        $this->dropTable('{{%login_attempts}}');
    }
}
