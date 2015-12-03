<?php

use yii\db\Schema;
use yii\db\Migration;

class m151202_075515_lock_out_policy extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'login_attempts', Schema::TYPE_SMALLINT . ' DEFAULT 0');
        $this->addColumn('{{%user}}', 'locked_until', Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'login_attempts');
        $this->dropColumn('{{%user}}', 'locked_until');
    }
}
