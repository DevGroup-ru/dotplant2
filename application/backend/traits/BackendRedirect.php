<?php

namespace app\backend\traits;


use Yii;
use yii\helpers\Url;

/**
 * Trait BackendRedirect serves special backend redirects
 * @package app\backend\traits
 */
trait BackendRedirect
{
    /**
     * Redirects user as was specified by his action and returnUrl variable
     * @param string|integer $id Id of model
     * @param bool|string $setFlash True if set standard flash, string for custom flash, false for not setting any flash
     * @param string $indexAction route path to index action
     * @param string $editAction route path to edit action
     * @return \yii\web\Response
     */
    public function redirectUser($id, $setFlash = true, $indexAction = 'index', $editAction='edit')
    {
        /** @var \app\backend\components\BackendController $this */
        if ($setFlash === true) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
        } elseif (is_string($setFlash)) {
            Yii::$app->session->setFlash('info', $setFlash);
        }

        $returnUrl = Yii::$app->request->get(
            'returnUrl',
            [$indexAction, 'id' => $id]
        );

        switch (Yii::$app->request->post('action', 'save')) {
            case 'next':
                return $this->redirect(
                    [
                        $editAction,
                        'returnUrl' => $returnUrl,
                    ]
                );
            case 'back':
                return $this->redirect($returnUrl);
            default:
                return $this->redirect(
                    Url::toRoute(
                        [
                            $editAction,
                            'id' => $id,
                            'returnUrl' => $returnUrl,
                        ]
                    )
                );
        }
    }
}