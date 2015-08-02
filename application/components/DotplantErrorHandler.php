<?php

namespace app\components;

use app\models\ErrorLog;
use app\models\ErrorUrl;
use Yii;
use yii\validators\EmailValidator;
use yii\web\ErrorHandler;

class DotplantErrorHandler extends ErrorHandler
{
    /**
     * @inheritdoc
     * @param \Exception $exception
     */
    protected function renderException($exception)
    {
        parent::renderException($exception);
        $status_code = 0;

        if (Yii::$app->has('response')) {
            $status_code = Yii::$app->getResponse()->statusCode;
        }

        $this->saveErrorInfo($exception, $status_code);
    }

    /**
     * @param $exception
     * @param $status_code
     */
    protected function saveErrorInfo($exception, $status_code)
    {
        $errorLogEnabled = Yii::$app->getModule('core')->errorMonitorEnabled;
        if (null !== $errorLogEnabled && $errorLogEnabled == 1) {
            $errorUrl = ErrorUrl::find()->where(['url' => $_SERVER['REQUEST_URI']])->one();
            if (is_null($errorUrl)) {
                $errorUrl = new ErrorUrl();
                $errorUrl->url = $_SERVER['REQUEST_URI'];
                $errorUrl->save();
            }
            $errorLog = new ErrorLog();
            $errorLog->info = $exception->getMessage();
            $errorLog->url_id = $errorUrl->id;
            $errorLog->http_code = $status_code;
            $errorLog->server_vars = print_r($_SERVER, true);
            $errorLog->request_vars = print_r(
                [
                    'request' => $_REQUEST,
                    'post' => $_POST,
                    'get' => $_GET
                ],
                true
            );
            $errorLog->save();
            $this->sendImmediateNotify($errorLog, $status_code);
        }
    }

    /**
     * @param $errorLog
     * @param $status_code
     */
    protected function sendImmediateNotify($errorLog, $status_code)
    {
        $immediateNotifyEnabled = Yii::$app->getModule('core')->immediateNotice;
        if ($immediateNotifyEnabled == 1) {
            $httpCodesForNotify = explode(",", Yii::$app->getModule('core')->httpCodesForImmediateNotify);
            if (strlen($httpCodesForNotify[0]) > 0) {
                if (!in_array($status_code, $httpCodesForNotify)) {
                    return;
                }
            }
            $notifyEmail = Yii::$app->getModule('core')->devmail;
            $validator = new EmailValidator();
            if ($validator->validate($notifyEmail)) {
                $errorUrl = $errorLog->getErrorUrl()->one();
                if ($errorUrl->immediate_notify_count < Yii::$app->getModule('core')->immediateNoticeLimitPerUrl) {
                    $errorUrl->immediate_notify_count++;
                    $errorUrl->update();
                    $info = [
                        'url' => $errorUrl->url,
                        'message' => $errorLog->info,
                        'http_code' => $errorLog->http_code,
                        'request_vars' => $errorLog->request_vars,
                        'server_vars' => $errorLog->server_vars
                    ];
                    try {
                        Yii::$app->mail->compose(
                            '@app/views/notifications/immediate_notify.php',
                            [
                                'info' => $info
                            ]
                        )
                            ->setTo($notifyEmail)
                            ->setFrom(Yii::$app->mail->transport->getUsername())
                            ->setSubject("ErrorMonitor immediate notify")
                            ->send();
                    } catch (\Exception $e) {
                        // do nothing
                    }
                }
            }
        }
    }
}
