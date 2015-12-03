<?php

use yii\db\Schema;
use yii\db\Migration;

class m151203_072425_add_require_password_change_field extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}', 'require_password_change', Schema::TYPE_SMALLINT . ' DEFAULT 1');
        $this->execute('UPDATE {{%user}} SET require_password_change = 0 WHERE last_login IS NOT NULL');
    }

    public function down()
    {
        $this->dropColumn('{{%user}}', 'require_password_change');
    }
}
