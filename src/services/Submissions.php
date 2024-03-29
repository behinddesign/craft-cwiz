<?php

namespace behinddesign\cwiz\services;

use behinddesign\cwiz\Cwiz;
use behinddesign\cwiz\Cwiz as CwizPlugin;
use behinddesign\cwiz\elements\Submissions as SubmissionsElement;
use behinddesign\cwiz\records\Submissions as SubmissionsRecord;
use Craft;
use craft\base\Element;
use craft\web\User;
use Exception;
use yii\base\Component;
use yii\web\NotFoundHttpException;


class Submissions extends Component
{
    protected $useCurrentUser = true;
    protected $submissionId;

    public function currentUser($useCurrentUser)
    {
        $this->useCurrentUser = $useCurrentUser;

        return $this;
    }

    public function submissionId($submissionId)
    {
        $this->submissionId = $submissionId;

        return $this;
    }

    public function blocks(Element $block): Blocks
    {
        $where = [
            'quizElementId' => $block->owner->owner->parent->id,
            'archived' => 0
        ];

        if (Craft::$app->getUser() && $this->useCurrentUser) {
            $where['userId'] = Craft::$app->getUser()->getId();
        }

        if ($this->submissionId) {
            $where['cwiz_submissions.id'] = $this->submissionId;
        }

        $submissionElement = SubmissionsElement::find()
            ->where($where);

        $submission = $submissionElement->one();

        return CwizPlugin::$plugin->getBlocks($block)->setSubmission($submission);
    }

    public function summary(Element $quizElement)
    {
        $where = [
            'quizElementId' => $quizElement->id,
            'archived' => 0
        ];

        if (Craft::$app->getUser() && $this->useCurrentUser) {
            $where['userId'] = Craft::$app->getUser()->getId();
        }

        if ($this->submissionId) {
            $where['cwiz_submissions.id'] = $this->submissionId;
        }

        $submissionElement = SubmissionsElement::find()
            ->where($where);

        $submission = $submissionElement->one();

        return CwizPlugin::$plugin->getSummary($quizElement)->setSubmission($submission);
    }

    public function findQuizSubmissionByUser($userId, $quizId, $archived = false): ?SubmissionsElement
    {
        return SubmissionsElement::find()
            ->where([
                'userId' => $userId,
                'quizElementId' => $quizId,
                'archived' => $archived
            ])
            ->one();
    }

    /**
     * @param SubmissionsElement $submissionsElement
     * @param bool $runValidation
     * @return bool
     * @throws NotFoundHttpException
     */
    public function save(SubmissionsElement $submissionsElement = null): bool
    {
        $isNew = !$submissionsElement->id;

        if (!$isNew) {
            $submissionsRecord = SubmissionsRecord::findOne($submissionsElement->id);

            if (!$submissionsRecord) {
                throw new NotFoundHttpException(Cwiz::t('Quiz not found'));
            }
        } else {
            $submissionsRecord = new SubmissionsRecord();
        }

        $fieldLayout = $submissionsElement->getFieldLayout();

        $transaction = Craft::$app->db->beginTransaction();

        try {
            Craft::$app->getFields()->saveLayout($fieldLayout);
            $submissionsElement->fieldLayoutId = $fieldLayout->id;
            $submissionsRecord->fieldLayoutId = $fieldLayout->id;

            if ($isNew) {
                $submissionsRecord->id = $submissionsElement->id;

                $fieldLayout = $submissionsElement->getFieldLayout();

                // Save field layout
                Craft::$app->getFields()->saveLayout($fieldLayout);
            }

            if (!Craft::$app->getElements()->saveElement($submissionsElement)) {
                return false;
            }

            $transaction->commit();

        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }

    public function archive(SubmissionsElement $submissionsElement = null): bool
    {
        $transaction = Craft::$app->db->beginTransaction();

        try {
            $submissionsElement->archived = 1;

            if (!Craft::$app->getElements()->saveElement($submissionsElement)) {
                return false;
            }

            $transaction->commit();

        } catch (Exception $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;
    }
}