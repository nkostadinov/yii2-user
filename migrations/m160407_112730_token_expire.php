<?php

use nkostadinov\user\models\Token;
use yii\db\Migration;

class m160407_112730_token_expire extends Migration
{
    public function up()
    {
        $this->alterColumn(Token::tableName(), 'expires_on', $this->integer()->defaultValue(0));
    }

    public function down()
    {
        $this->alterColumn(Token::tableName(), 'expires_on', $this->integer()->notNull());
    }

}
