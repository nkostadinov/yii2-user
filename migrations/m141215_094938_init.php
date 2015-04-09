<?php

//namespace nkostadinov\user\migrations;

use nkostadinov\user\migrations\Migration;
use yii\db\Schema;

class m141215_094938_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%user}}', [
            'id' => Schema::TYPE_PK,
            'username' => Schema::TYPE_STRING,
            'auth_key' => Schema::TYPE_STRING . '(32) NOT NULL',
            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'email' => Schema::TYPE_STRING . ' NOT NULL',
            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'confirmed_on' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
            'register_ip' => Schema::TYPE_STRING . '(45) NOT NULL',
            'last_login' => Schema::TYPE_INTEGER . ' DEFAULT NULL',
            'last_login_ip' => Schema::TYPE_STRING . '(45)',
        ], $this->getTableOptions());
        //Create unique index for email field
        $this->createIndex('unq_email', '{{%user}}', 'email', true);

        //This table holds the linked accounts of the user
        $this->createTable('{{%user_account}}', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER,
            'provider' => Schema::TYPE_STRING,
            'attributes' => Schema::TYPE_TEXT,
            'access_token' => Schema::TYPE_STRING,
            'expires' => Schema::TYPE_INTEGER,
            'token_create_time' => Schema::TYPE_INTEGER,
            'client_id' => Schema::TYPE_BIGINT . ' NOT NULL',
            'created_at' => Schema::TYPE_TIMESTAMP . ' DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => Schema::TYPE_TIMESTAMP,
        ], $this->getTableOptions());
        //FK to user
        $this->addForeignKey('fk_useraccount_user', '{{%user_account}}', 'user_id', '{{%user}}', 'id');
    }

    public function down()
    {
        $this->dropTable('{{%user_account}}');
        $this->dropTable('{{%user}}');
    }
}
