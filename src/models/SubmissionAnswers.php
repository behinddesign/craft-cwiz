<?php

namespace behinddesign\cwiz\models;

use craft\base\Model;

class SubmissionAnswers extends Model
{
    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int Submission Reference Id
     */
    public $submissionId;

    /**
     * @var int Question Element Id
     */
    public $questionElementId;

    /**
     * @var int Answer Element ID
     */
    public $answerElementId;

    /**
     * @var int Option Element ID
     */
    public $optionElementId;

    /**
     * @var string Textual answer
     */
    public $answer;

    /**
     * @var bool Whether the answer is correct
     */
    public $isCorrect;

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