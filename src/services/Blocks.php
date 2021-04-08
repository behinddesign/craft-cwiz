<?php

namespace behinddesign\cwiz\services;

use behinddesign\cwiz\elements\Submissions;
use craft\base\Element;
use yii\base\Component;

class Blocks extends Component
{
    /** @var Submissions */
    protected $submission;

    /** @var Element */
    protected $element;

    public function setBlock(Element $block): Blocks
    {
        $this->element = $block;

        return $this;
    }

    public function setSubmission(Submissions $submission = null): Blocks
    {
        $this->submission = $submission;

        return $this;
    }

    public function checked(Element $option)
    {
        if (!$this->isOptionFound($option)) {
            return null;
        }

        return 'checked';
    }

    public function selected(Element $option)
    {
        if (!$this->isOptionFound($option)) {
            return null;
        }

        return 'selected';
    }

    public function value()
    {
        $foundAnswer = $this->isAnswerFound($this->element);
        if (!$foundAnswer) {
            return null;
        }

        return $foundAnswer;
    }

    public function isCorrect()
    {
        //If there's no submissions, make sure all answers are marked correct so it doesn't trigger errors
        if (empty($this->submission) || empty($this->submission->answers())) {
            return null;
        }

        $questionId = $this->element->owner->owner->id;

        $answers = $this->submission->answers();
        if (empty($answers[$questionId][$this->element->id])) {
            return null;
        }

        if (!isset($this->element->options)) {
            if ($answers[$questionId][$this->element->id]['textAnswer']) {
                return true;
            } else {
                return false;
            }
        }

        $correct = true;
        foreach ($this->element->options as $option) {
            //The option is marked as correct but the submission does not exist, mark as incorrect
            if (
                $option->correct &&
                !isset($answers[$questionId][$this->element->id]['textAnswer']) &&
                array_search($option->id, $answers[$questionId][$this->element->id]['answer']) === false
            ) {
                $correct = false;
            } elseif (
                !$option->correct &&
                !isset($answers[$questionId][$this->element->id]['textAnswer']) &&
                array_search($option->id, $answers[$questionId][$this->element->id]['answer']) !== false
            ) {
                $correct = false;
            }
        }

        return $correct;
    }

    public function hasAnswered()
    {
        if (empty($this->submission) || empty($this->submission->answers())) {
            return false;
        }

        $answers = $this->submission->answers();
        foreach ($answers as $answer) {
            if (!empty($answer[$this->element->id])) {
                return true;
            }
        }

        return true;
    }

    public function correctAnswer()
    {
        if (empty($this->element->options)) {
            throw new \Exception('There are no options available for this block');
        }

        foreach ($this->element->options as $option) {
            if ($option->correct) {
                return $option->option;
            }
        }
    }

    public function correctAnswers(): array
    {
        if (empty($this->element->options)) {
            throw new \Exception('There are no options available for this block');
        }

        $answers = [];
        foreach ($this->element->options as $option) {
            if ($option->correct) {
                $answers[] = $option->option;
            }
        }

        return $answers;
    }

    protected function isOptionFound(Element $option)
    {
        if (empty($this->submission) || empty($this->submission->answers())) {
            return false;
        }

        foreach ($this->submission->answers() as $questionIds) {
            foreach ($questionIds as $questionId) {
                if (empty($questionId['answer'])) {
                    continue;
                }

                foreach ($questionId['answer'] as $answer) {
                    if ($answer == $option->id) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    protected function isAnswerFound(Element $answer)
    {
        if (empty($this->submission) || empty($this->submission->answers())) {
            return null;
        }

        foreach ($this->submission->answers() as $questionIds) {
            if (!empty($questionIds[$answer->id]['textAnswer'])) {
                return $questionIds[$answer->id]['textAnswer'];
            }
        }

        return null;
    }
}