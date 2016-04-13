<?php

use yii\db\Migration;

class m160411_134641_fix_password_hash extends Migration
{
    public function up()
    {
        $this->alterColumn(\nkostadinov\user\models\User::tableName(), 'password_hash', $this->string());
    }

    public function down()
    {
        $this->alterColumn(\nkostadinov\user\models\User::tableName(), 'password_hash', $this->string()->notNull());
    }

}
