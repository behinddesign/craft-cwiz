<?php

namespace behinddesign\cwiz\controllers;

use behinddesign\cwiz\Cwiz;
use Craft;
use craft\elements\Entry;
use craft\web\Response;
use behinddesign\cwiz\elements\Submissions as SubmissionsElement;
use yii\web\ForbiddenHttpException;

class SubmissionsController extends Controller
{
    public function actionIndex(): Response
    {
        $perms = Craft::$app->getUser()->checkPermission('cwiz-viewSubmissions');

        if (!$perms) {
            return $this->deniedIndex();
        }

        return $this->renderTemplate('submissions/index', [
            'hasPermission' => $perms
        ]);
    }

    public function deniedIndex(): Response
    {
        return $this->renderTemplate('submissions/denied');
    }

    public function actionView($submissionId): Response
    {
        // Check against permissions to save at all, or per-form
        if (!Craft::$app->getUser()->checkPermission('cwiz-viewSubmissions')) {
            throw new ForbiddenHttpException('User is not permitted to perform this action');
        }

        $submission = SubmissionsElement::find()
            ->where(['cwiz_submissions.id' => $submissionId])
            ->one();

        $quiz = Entry::findOne($submission->quizElementId);

        return $this->renderTemplate('submissions/view', [
            'quizElement' => $quiz,
            'submissions' => $submission
        ]);
    }

    /**
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave(): ?Response
    {
        $this->requirePostRequest();

        $submission = $this->processSubmission();

        if (!Cwiz::$plugin->getSubmissions()->save($submission)) {
            throw new \Exception('Quiz not saved');
        }

        return $this->redirectToPostedUrl();
    }

    public function actionReset(): ?Response
    {
        $this->requirePostRequest();

        $quizElementId = Craft::$app->getRequest()->getBodyParam('quizElementId');

        if (empty($quizElementId)) {
            throw new \Exception('Input quizElementId must be set');
        }

        $submission = SubmissionsElement::find()
            ->where([
                'userId' => Craft::$app->getUser()->getId(),
                'quizElementId' => $quizElementId,
                'archived' => 0
            ])
            ->one();

        if (empty($submission)) {
            return null;
        }

        if (!Cwiz::$plugin->getSubmissions()->archive($submission)) {
            throw new \Exception('Quiz not archived');
        }

        return $this->redirectToPostedUrl();
    }

    protected function processSubmission()
    {
        $submission = Cwiz::getInstance()->getSubmissions()->findQuizSubmissionByUser(
            Craft::$app->getUser()->getId(),
            Craft::$app->getRequest()->getBodyParam('quizElementId')
        );

        if (!$submission) {
            $submission = new SubmissionsElement();
        }

        $submission->quizElementId = Craft::$app->getRequest()->getBodyParam('quizElementId');
        $submission->userId = Craft::$app->getUser()->getId();
        $submission->archived = 0;

        return $submission;
    }

    protected function populate($quizElementId)
    {
        if (empty($quizElementId)) {
            return new SubmissionsElement();
        }

        $submission = SubmissionsElement::find()
            ->where([
                'userId' => Craft::$app->getUser()->getId(),
                'quizElementId' => $quizElementId,
                'archived' => 0
            ])
            ->one();

        if (empty($submission)) {
            return new SubmissionsElement();
        }

        return $submission;
    }
}