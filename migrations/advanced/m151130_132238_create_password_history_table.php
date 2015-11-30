<?php

use yii\db\Schema;
use nkostadinov\user\migrations\Migration;

class m151130_132238_create_password_history_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%password_history}}', [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'password_hash' => Schema::TYPE_STRING . '(255) NOT NULL',
            'created_at' => Schema::TYPE_TIMESTAMP . ' NULL DEFAULT CURRENT_TIMESTAMP',
        ], $this->getTableOptions());
        $this->addForeignKey('password_history_user_fk', 'password_history', 'user_id', 'user', 'id', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('password_history');
    }
}
