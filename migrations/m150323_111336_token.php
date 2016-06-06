<?php

use nkostadinov\user\migrations\Migration;
use yii\db\Schema;

class m150323_111336_token extends Migration
{
    public function up()
    {
        $this->createTable('{{%token}}', [
            'user_id'    => Schema::TYPE_INTEGER . ' NOT NULL',
            'code'       => Schema::TYPE_STRING . '(40) NOT NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'type'       => Schema::TYPE_SMALLINT . ' NOT NULL',
            'expires_on' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $this->getTableOptions());

        $this->createIndex('token_unique', '{{%token}}', ['user_id', 'code', 'type'], true);
        $this->addForeignKey('fk_user_token', '{{%token}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'RESTRICT');
    }
    public function down()
    {
        $this->dropTable('{{%token}}');
    }
}
