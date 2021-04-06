<?php

namespace behinddesign\cwiz\base;

use behinddesign\cwiz\services\Blocks;
use behinddesign\cwiz\services\Cwiz;
use behinddesign\cwiz\services\Submissions;
use behinddesign\cwiz\services\Summary;
use behinddesign\cwiz\services\Validation;
use craft\base\Element;

trait PluginTrait
{
    public function getSummary(Element $quiz): Summary
    {
        return $this->get('summary')->setQuiz($quiz);
    }

    public function getBlocks(Element $block): Blocks
    {
        return $this->get('blocks')->setBlock($block);
    }

    public function getSubmissions(): Submissions
    {
        return $this->get('submissions');
    }

    public function getValidation(): Validation
    {
        return $this->get('validation');
    }

    public function getCwiz(): Cwiz
    {
        return $this->get('cwiz');
    }
}