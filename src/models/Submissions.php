<?php

namespace behinddesign\cwiz\models;

use craft\base\Model;

class Submissions extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int Element ID of the Quiz
     */
    public $quizElementId;

    /**
     * @var int User Reference Id
     */
    public $userId;

    /**
     * @var int Field Layouts
     */
    public $fieldLayoutId;

    /**
     * @var string
     */
    public $dateCreated;

    /**
     * @var string
     */
    public $dateUpdated;

    /**
     * @var string
     */
    public $uid;
}