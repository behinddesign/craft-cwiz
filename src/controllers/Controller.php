<?php

namespace behinddesign\cwiz\controllers;

use Craft;
use craft\web\Controller as BaseController;
use yii\web\Response as YiiResponse;

abstract class Controller extends BaseController
{
    public function init()
    {
        parent::init();

        $response = Craft::$app->getResponse();
        $headers = $response->getHeaders();
        $headers->set('Cache-Control', 'private');
    }

    public function renderTemplate(string $template, array $variables = [], string $templateMode = null): YiiResponse
    {
        return parent::renderTemplate('cwiz/' . $template, $variables, $templateMode);
    }
}