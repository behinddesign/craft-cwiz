<?php

namespace behinddesign\cwiz\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubmissionsQuery extends ElementQuery
{
    public $id;
    public $quizElementId;

    protected function beforePrepare(): bool
    {
        $this->joinElementTable('cwiz_submissions');

        $this->query->select([
            'cwiz_submissions.id',
            'cwiz_submissions.userId',
            'cwiz_submissions.quizElementId'
        ]);

        if ($this->id) {
            $this->subQuery->andWhere(Db::parseParam('cwiz_submissions.id', $this->id));
        }

        return parent::beforePrepare();
    }
}