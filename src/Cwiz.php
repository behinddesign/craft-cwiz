<?php

namespace behinddesign\cwiz;

use behinddesign\cwiz\base\PluginTrait;
use behinddesign\cwiz\elements\Submissions as SubmissionsElement;
use behinddesign\cwiz\models\Settings;
use behinddesign\cwiz\services\Summary;
use behinddesign\cwiz\services\Validation;
use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterElementActionsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\services\Elements;
use craft\web\twig\variables\Cp;
use behinddesign\cwiz\services\Cwiz as CwizService;
use behinddesign\cwiz\services\Question as QuestionService;
use behinddesign\cwiz\services\Submissions as SubmissionsService;
use behinddesign\cwiz\services\Blocks as BlocksService;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use yii\base\Event;

class Cwiz extends Plugin
{
    use PluginTrait;

    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Cwiz::$plugin
     *
     * @var Cwiz
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '0.1.0';

    /**
     * @var string
     */
    public $minVersionRequired = '3.5.1';

    /**
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @var bool
     */
    public $hasCpSettings = false;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        \Yii::setAlias('@cwiz', __DIR__);

        $this->registerComponents();
        $this->registerElements();
        $this->registerElementActions();
        $this->registerVariables();
        $this->registerCpNav();
        $this->registerCpRoutes();
        $this->handleEvents();

        Craft::info(
            Craft::t(
                'cwiz',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getPluginName()
    {
        return Craft::t('cwiz', $this->getSettings()->pluginName);
    }

    private function registerCpNav()
    {
        Event::on(
            Cp::class,
            Cp::EVENT_REGISTER_CP_NAV_ITEMS,
            function (RegisterCpNavItemsEvent $event) {
                $event->navItems[] = [
                    'url' => 'cwiz/submissions',
                    'label' => 'Cwiz',
                    'icon' => '@cwiz/icon.svg',
                    /*'subnav' => [
                        'submissions' => ['label' => 'Submissions', 'url' => 'cwiz/submissions'],
                    ]*/
                ];
            }
        );
    }

    private function registerVariables()
    {
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;

                $variable->set('cwiz', CwizService::class);
            }
        );
    }

    private function handleEvents()
    {

    }

    private function registerCpRoutes()
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cwiz/submissions'] = 'cwiz/submissions/index';
                $event->rules['cwiz/submissions/<submissionId:\d+>/view'] = 'cwiz/submissions/view';
                $event->rules['cwiz/submissions/<submissionId:\d+>/delete'] = 'cwiz/quizzes/delete';

                //Landing page, default to quizzes
                $event->rules['cwiz'] = $event->rules['cwiz/submissions'];
            }
        );
    }

    private function registerComponents()
    {
        $this->setComponents([
            'cwiz' => CwizService::class,
            'summary' => Summary::class,
            'submissions' => SubmissionsService::class,
            'validation' => Validation::class,
            'blocks' => BlocksService::class
        ]);
    }

    private function registerElements()
    {
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = SubmissionsElement::class;
            }
        );
    }

    private function registerElementActions()
    {
        /*Event::on(
            EntryElement::class,
            Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {

            }
        );*/
    }

    //Localisation

    /**
     * @param $message
     * @param array $params
     * @return string
     */
    public static function t($message, array $params = []): string
    {
        return Craft::t('cwiz', $message, $params);
    }

    //Settings

    public function getSettingsResponse()
    {

    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate('cwiz/settings', [
                'settings' => $this->getSettings()
            ]
        );
    }
}
