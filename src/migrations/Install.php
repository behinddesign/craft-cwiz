<?php

namespace behinddesign\cwiz\migrations;

use behinddesign\cwiz\records\SubmissionAnswers;
use behinddesign\cwiz\records\Submissions;
use craft\db\Migration;

/**
 * Initial migration
 *
 * Quick up/down : php craft plugin/uninstall cwiz; php craft plugin/install cwiz
 *
 * Class Install
 * @package behinddesign\cwiz\migrations
 */
class Install extends Migration
{
    public function safeUp()
    {
        $this->addTables();
        $this->addForeignKeys();
        $this->addIndexes();
    }

    public function safeDown()
    {
        $this->dropTableIfExists(SubmissionAnswers::tableName());
        $this->dropTableIfExists(Submissions::tableName());
    }

    private function addTables()
    {
        if (!$this->db->tableExists(Submissions::tableName())) {
            $this->createTable(Submissions::tableName(), [
                'id' => $this->primaryKey(),
                'quizElementId' => $this->integer()->notNull(),
                'userId' => $this->integer(),
                'fieldLayoutId' => $this->integer()->notNull(),
//                'archived' => $this->boolean()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);
        }

        if (!$this->db->tableExists(SubmissionAnswers::tableName())) {
            $this->createTable(SubmissionAnswers::tableName(), [
                'id' => $this->primaryKey(),
                'submissionId' => $this->integer()->notNull(),
                'questionElementId' => $this->integer()->notNull(),
                'answerElementId' => $this->integer()->notNull(),
                'optionElementId' => $this->integer(),
                'answer' => $this->text(),
                'isCorrect' => $this->boolean(),
                'isTextAnswer' => $this->boolean()->notNull(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]);
        }
    }

    private function addForeignKeys()
    {
        //Submissions
        $this->addForeignKey(null, Submissions::tableName(), 'id', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, Submissions::tableName(), 'quizElementId', '{{%elements}}', 'id', 'CASCADE', null);
        $this->addForeignKey(null, Submissions::tableName(), 'userId', '{{%users}}', 'id', 'SET NULL', null);
        $this->addForeignKey(null, Submissions::tableName(), 'fieldLayoutId', '{{%fieldlayouts}}', 'id', 'CASCADE');

        //Submission Answers
        $this->addForeignKey(null, SubmissionAnswers::tableName(), 'submissionId', Submissions::tableName(), 'id', 'CASCADE', null);
        $this->addForeignKey(null, SubmissionAnswers::tableName(), 'questionElementId', '{{%elements}}', 'id', 'CASCADE', null);
        //$this->addForeignKey(null, SubmissionAnswers::tableName(), 'answerElementId', '{{%elements}}', 'id', 'CASCADE', null);
    }

    private function addIndexes()
    {
        //Submissions
        $this->createIndex(null, Submissions::tableName(), 'quizElementId', false);
        $this->createIndex(null, Submissions::tableName(), 'userId', false);
        $this->createIndex(null, Submissions::tableName(), 'fieldLayoutId', false);

        //Submission Answers
        $this->createIndex(null, SubmissionAnswers::tableName(), 'submissionId', false);
        $this->createIndex(null, SubmissionAnswers::tableName(), 'questionElementId', false);
        $this->createIndex(null, SubmissionAnswers::tableName(), 'answerElementId', false);
        $this->createIndex(null, SubmissionAnswers::tableName(), 'isCorrect', false);
    }
}