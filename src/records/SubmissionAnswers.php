<?php

namespace behinddesign\cwiz\records;

use craft\db\ActiveRecord;

class SubmissionAnswers extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%cwiz_submissionanswers}}';
    }

    public static function bySubmissionId($submissionId)
    {
        $results = self::find()
            ->where([
                'submissionId' => $submissionId
            ])
            ->all();

        if (empty($results)) {
            return null;
        }

        $structuredResults = self::structureResults($results);

        return $structuredResults;
    }

    protected static function structureResults($results)
    {
        if (empty($results)) {
            return null;
        }

        $questionAnswers = [];
        foreach ($results as $result) {
            if (!empty($result['optionElementId'])) {
                $questionAnswers[$result->questionElementId][$result->answerElementId]['answer'][] = $result['optionElementId'];
            } else {
                $questionAnswers[$result->questionElementId][$result->answerElementId]['textAnswer'] = $result['answer'];
            }
        }

        return $questionAnswers;
    }
}