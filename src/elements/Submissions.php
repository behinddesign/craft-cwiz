<?php

namespace behinddesign\cwiz\elements;

use behinddesign\cwiz\Cwiz;
use behinddesign\cwiz\elements\db\SubmissionsQuery;
use behinddesign\cwiz\records\SubmissionAnswers as SubmissionAnswersRecord;
use behinddesign\cwiz\records\Submissions as SubmissionsRecord;
use behinddesign\cwiz\services\Summary;
use Craft;
use craft\base\Element;
use craft\behaviors\FieldLayoutBehavior;
use craft\elements\db\ElementQueryInterface;
use craft\elements\Entry;
use craft\elements\MatrixBlock;
use craft\elements\User as UserElement;
use craft\helpers\UrlHelper;
use Exception;
use verbb\supertable\elements\SuperTableBlockElement;

class Submissions extends Element
{
    public const EVENT_ALLOWED_FIELD_TYPES = 'allowedFieldTypes';

    // Constants
    // =========================================================================

    public const STATUS_ENABLED = 'enabled';
    public const STATUS_ARCHIVED = 'archived';

    // Properties
    // =========================================================================
    public $quizElementId;
    public $userId;
    public $fieldLayoutId;

    // Variables to hold data to stop re-querying
    /** @var SubmissionAnswersRecord[] */
    protected $answers;

    /** @var UserElement */
    protected $user;


    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Cwiz::t('Submission');
    }

    /**
     * @return string
     */
    public static function pluralDisplayName(): string
    {
        return Cwiz::t('Submissions');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'submissions';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public static function gqlTypeNameByContext($context): string
    {
        return 'Submissions';
    }

    /**
     * @inheritdoc
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_ENABLED => Cwiz::t('Current'),
            self::STATUS_ARCHIVED => Cwiz::t('Archived')
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFieldContext(): string
    {
        return 'submissions:' . $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getCpEditUrl()
    {
        return UrlHelper::cpUrl(
            'cwiz/submissions/' . $this->id . '/view'
        );
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string) $this->id;
    }

    /**
     * @inheritdoc
     *
     * @return SubmissionsQuery The newly created [[FormQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new SubmissionsQuery(get_called_class());
    }

    /**
     * @inheritDoc
     */
    public static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Cwiz::t('All Submissions'),
                'defaultSort' => ['id', 'desc']
            ]
        ];

        $sources[] = ['heading' => Cwiz::t('Quizzes')];

        $quizzes = self::distinctQuizzes();
        if ($quizzes) {
            foreach ($quizzes as $quiz) {
                $key = "quizElementId:{$quiz->id}";

                $sources[$key] = [
                    'key' => $key,
                    'label' => $quiz->title,
                    'data' => [
                        'quizElementId' => $quiz->id,
                    ],
                    'criteria' => ['quizElementId' => $quiz->id],
                    'defaultSort' => ['id', 'desc'],
                ];
            }
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['fieldLayout'] = [
            'class' => FieldLayoutBehavior::class,
            'elementType' => static::class
        ];

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function getFieldLayout()
    {
        $behavior = $this->getBehavior('fieldLayout');

        return $behavior->getFieldLayout();
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return parent::getStatus();
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'userId':
                return $this->user()->name;
            case 'quizElementId':
                $quiz = Entry::find()
                    ->where(['entries.id' => $this->quizElementId])
                    ->one();

                if (empty($quiz)) {
                    return '';
                }

                return $quiz->title;
        }

        return parent::tableAttributeHtml($attribute);
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['id', 'userId'];
    }

    /**
     * @inheritDoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            [
                'label' => Cwiz::t('ID'),
                'orderBy' => 'elements.id',
                'attribute' => 'id',
            ],
            [
                'label' => Cwiz::t('User Id'),
                'orderBy' => 'cwiz_submissions.userId',
                'attribute' => 'userId',
            ],
            [
                'label' => Cwiz::t('Date Created'),
                'orderBy' => 'elements.dateCreated',
                'attribute' => 'dateCreated'
            ],
            [
                'label' => Cwiz::t('Date Updated'),
                'orderBy' => 'elements.dateUpdated',
                'attribute' => 'dateUpdated'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        return [
            'id' => ['label' => Cwiz::t('ID')],
            'userId' => ['label' => Cwiz::t('User')],
            'quizElementId' => ['label' => Cwiz::t('Quiz')]
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['id', 'userId', 'quizElementId'];
    }

    /**
     * @inheritdoc
     */
    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
            $record = SubmissionsRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid submissions ID: ' . $this->id);
            }
        } else {
            $record = new SubmissionsRecord();
            $record->id = $this->id;
        }

        $record->id = $this->id;
        $record->quizElementId = $this->quizElementId;
        $record->fieldLayoutId = $this->fieldLayoutId;
        $record->userId = $this->userId;

        $record->save(false);

        //Clean up items which also do not have an answer.
        if (Craft::$app->getRequest()->getBodyParam('questionElementId')) {
            SubmissionAnswersRecord::deleteAll(
                'questionElementId = :questionElementId' .
                ' AND submissionId = :submissionId',
                [
                    ':questionElementId' => Craft::$app->getRequest()->getBodyParam('questionElementId'),
                    ':submissionId' => $this->id
                ]
            );
        }

        $formGroupOfAnswerIdsAndOptionIds = Craft::$app->getRequest()->getBodyParam('answer');

        if (!empty($formGroupOfAnswerIdsAndOptionIds)) {
            foreach ($formGroupOfAnswerIdsAndOptionIds as $formAnswerIds => $formOptionIds) {
                $answerElement = MatrixBlock::find()->id($formAnswerIds)->one();
                $questionElement = $answerElement->getOwner()->getOwner(); //ew?

                if (!$answerElement) {
                    throw new Exception('Cant find answerElement');
                }

                //Delete all existing options for this answer.
                SubmissionAnswersRecord::deleteAll(
                    'answerElementId = :answerElementId' .
                    ' AND submissionId = :submissionId',
                    [
                        ':answerElementId' => $answerElement->id,
                        ':submissionId' => $this->id
                    ]
                );

                if (!is_array($formOptionIds)) {
                    $formOptionIds = [$formOptionIds]; //Force it into a standard array.
                }

                foreach ($formOptionIds as $formOptionId) {
                    $optionElement = SuperTableBlockElement::find()->id($formOptionId)->one();
                    if ($optionElement) {
                        if (empty($optionElement->id)) {
                            continue;
                        }

                        $optionElementId = $optionElement->id;
                        $isCorrect = $optionElement->correct;
                        $isTextAnswer = false;
                        $answer = null;
                    } else {
                        if (empty($formOptionId)) {
                            continue;
                        }

                        $optionElementId = null;
                        $answer = $formOptionId;
                        $isTextAnswer = true;
                        $isCorrect = null;
                    }

                    $answerRecord = $this->findAnswerFromElements(
                        $record->id,
                        $questionElement->id,
                        $answerElement->id,
                        !empty($optionElement->id) ? $optionElement->id : null,
                        $answer
                    );

                    if (!$answerRecord) {
                        $answerRecord = new SubmissionAnswersRecord();
                    }

                    $answerRecord->submissionId = $record->id;
                    $answerRecord->questionElementId = $questionElement->id;
                    $answerRecord->answerElementId = $answerElement->id;
                    $answerRecord->optionElementId = $optionElementId;
                    $answerRecord->answer = $answer;
                    $answerRecord->isCorrect = $isCorrect;
                    $answerRecord->isTextAnswer = $isTextAnswer;

                    if (!$answerRecord->save()) {
                        throw new Exception('failed to save answer record');
                    }
                }
            }

            //This is the preferred method, but commented for now.
            /*
            //Now delete any options which are stored which haven't been selected.
            $answers = $this->answers();

            if (!empty($answers)) {
                foreach ($answers as $questionIds) {
                    foreach ($questionIds as $questionId) {
                        if (empty($questionId['answer'])) {
                            continue;
                        }

                        foreach ($questionId['answer'] as $answer) {
                            $find = array_search($answer, $inserted);
                            if ($find === false) {
                                SubmissionAnswersRecord::deleteAll(
                                    'optionElementId = ' . $answer . ' AND answerElementId = ' . $answerElement->id
                                );
                            }
                        }
                    }
                }
            }*/
        }

        parent::afterSave($isNew);
    }

    protected function findAnswerFromElements(
        $submissionId,
        $questionElementId,
        $answerElementId,
        $optionElementId,
        $textAnswer
    ): ?SubmissionAnswersRecord {
        $answers = SubmissionAnswersRecord::find()
            ->where([
                'submissionId' => $submissionId,
            ])->all();

        if (empty($answers)) {
            return null;
        }

        foreach ($answers as $answer) {
            if (
                $answer->questionElementId == $questionElementId &&
                $answer->answerElementId == $answerElementId &&
                (!empty($textAnswer) || empty($textAnswer) && $answer->optionElementId == $optionElementId)
            ) {
                return $answer;
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        Cwiz::$plugin->getSubmissions()->delete($this->id);

        parent::afterDelete();
    }

    public function answers(): ?array
    {
        if (!empty($this->answers)) {
            return $this->answers;
        }

        return SubmissionAnswersRecord::bySubmissionId($this->id);
    }

    public function summary(): ?Summary
    {
        return Cwiz::$plugin->getSummary()->setQuiz();
    }

    public function user(): UserElement
    {
        if (!empty($this->user)) {
            return $this->user;
        }

        return Craft::$app->getUsers()->getUserById($this->userId);
    }

    public static function distinctQuizzes()
    {
        $submissions = SubmissionsRecord::find()
            ->select('quizElementId')
            ->groupBy('quizElementId')
            ->all();

        if (empty($submissions)) {
            return null;
        }

        $quizIds = [];
        foreach ($submissions as $submission) {
            $quizIds[] = $submission->quizElementId;
        }

        return Entry::find()
            ->where(['in', 'elements.id', $quizIds])
            ->all();
    }
}