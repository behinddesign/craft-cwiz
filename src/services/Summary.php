<?php

namespace behinddesign\cwiz\services;

use behinddesign\cwiz\Cwiz as CwizPlugin;
use behinddesign\cwiz\elements\Submissions as SubmissionsElement;
use craft\base\Element;
use yii\base\Component;

class Summary extends Component
{
    /** @var SubmissionsElement */
    protected $submission;

    /** @var Element */
    protected $quiz;

    public function setQuiz(Element $quiz): Summary
    {
        $this->quiz = $quiz;

        return $this;
    }

    public function setSubmission(SubmissionsElement $submission = null): Summary
    {
        $this->submission = $submission;

        return $this;
    }

    public function numberOfQuestions()
    {
        return count($this->quiz->children);
    }



    public function completedQuestions()
    {
        //If there's no submissions, make sure all answers are marked correct so it doesn't trigger errors
        if (empty($this->submission) || empty($this->submission->answers())) {
            return 0;
        }

        $numberComplete = 0;
        $answers = $this->submission->answers();
        foreach ($this->quiz->children as $question) {
            $complete = true;
            foreach ($question->questionsAnswers as $subQuestions) {
                foreach ($subQuestions->answer as $subAnswer) {
                    if (!isset($answers[$question->id][$subAnswer->id])) {
                        $complete = false;
                    }
                }
            }

            if ($complete) {
                $numberComplete++;
            }
        }

        return $numberComplete;
    }

    public function hasSavedQuiz()
    {
        //If there's no submissions, make sure all answers are marked correct so it doesn't trigger errors
        if (empty($this->submission) || empty($this->submission->answers())) {
            return 0;
        }

        if (empty($this->submission->answers())) {
            return false;
        }

        return true;
    }

    public function hasSavedQuestion(Element $question)
    {
        //If there's no submissions, make sure all answers are marked correct so it doesn't trigger errors
        if (empty($this->submission) || empty($this->submission->answers())) {
            return false;
        }

        $answers = $this->submission->answers();
        if (!isset($answers[$question->id])) {
            return false;
        }

        return true;
    }

    public function numberOfSubQuestions()
    {
        $count = 0;
        foreach ($this->quiz->children as $questions) {
            foreach ($questions->questionsAnswers as $subQuestionAnswer) {
                foreach ($subQuestionAnswer->answer as $question) {
                    $count++;
                }
            }
        }

        return $count;
    }

    public function completedSubQuestions(Element $question)
    {
        //If there's no submissions, make sure all answers are marked correct so it doesn't trigger errors
        if (empty($this->submission) || empty($this->submission->answers())) {
            return 0;
        }

        $numberComplete = 0;
        $answers = $this->submission->answers();
        foreach ($question->questionsAnswers as $subQuestions) {
            $complete = true;

            foreach ($subQuestions->answer as $subAnswer) {
                if (!isset($answers[$question->id][$subAnswer->id])) {
                    $complete = false;
                }
            }

            if ($complete) {
                $numberComplete++;
            }
        }

        return $numberComplete;
    }

    public function correctSubQuestions()
    {
        //If there's no submissions, make sure all answers are marked correct so it doesn't trigger errors
        if (empty($this->submission) || empty($this->submission->answers())) {
            return 0;
        }

        $numberComplete = 0;
        foreach ($this->quiz->children as $question) {
            foreach ($question->questionsAnswers as $subQuestions) {
                foreach ($subQuestions->answer as $questions) {
                    if (CwizPlugin::$plugin->getBlocks($questions)->setSubmission($this->submission)->isCorrect()) {
                        $numberComplete++;
                    }
                }
            }
        }

        return $numberComplete;
    }
}