<?php

namespace behinddesign\cwiz\records;

use craft\db\ActiveRecord;

class Submissions extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%cwiz_submissions}}';
    }
}