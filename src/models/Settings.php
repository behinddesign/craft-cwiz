<?php

namespace behinddesign\cwiz\models;

use craft\base\Model;

class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $pluginName = 'Cwiz';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['pluginName', 'string'],
            ['pluginName', 'default', 'value' => 'Cwiz'],
        ];
    }
}
