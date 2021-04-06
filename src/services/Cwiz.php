<?php

namespace behinddesign\cwiz\services;

use craft\elements\Entry;
use yii\base\Component;
use behinddesign\cwiz\Cwiz as CwizPlugin;

class Cwiz extends Component
{
    public function getPluginName(): string
    {
        return CwizPlugin::$plugin->getPluginName();
    }

    public function submissions()
    {
        return CwizPlugin::$plugin->getSubmissions();
    }

    public function questions($questionElementId)
    {
        $questionElement = Entry::find()->id($questionElementId)->one();

        if (empty($questionElement)) {
            return null;
        }

        if (empty($questionElement->questionsAnswers)) {
            throw new \Exception('Please make sure the field "questionsAnswers" is available and is a SuperTable');
        }

        $subQuestions = $questionElement->questionsAnswers->all();

        $questions = [];
        foreach ($subQuestions as $subQuestion) {
            if (empty($subQuestion->answer)) {
                throw new \Exception('Please make sure the handle "answer" is available within "questionsAnswers" handle');
            }

            $answerElement = $subQuestion->answer[0];
            if ($answerElement->options) {
                foreach ($answerElement->options as $option) {
                    $questions[$subQuestion->id][] = $option;
                }
            } else {
                $questions[$subQuestion->id] = $answerElement;
            }
        }

        return $questions;
    }
}