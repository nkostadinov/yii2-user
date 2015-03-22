<?php
/**
 * Created by PhpStorm.
 * User: Phreak
 * Date: 20.11.2014
 * Time: 14:53 ч.
 */

namespace nkostadinov\user\migrations;

class Migration extends \yii\db\Migration
{
    public function getTableOptions()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            return 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
    }
}