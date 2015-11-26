<?php

use yii\db\Schema;
use yii\db\Migration;

class m151126_130152_add_password_changed_at extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'password_changed_at', Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('user', 'password_changed_at');
    }
}