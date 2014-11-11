<?php

namespace app\components;

use Yii;
use app\backend\models\ErrorMonitorConfig;
use app\models\ErrorLog;
use app\models\ErrorUrl;
use yii\validators\EmailValidator;
use yii\web\ErrorHandler;

class DotplantErrorHandler extends ErrorHandler
{
    protected function renderException($exception)
    {
        parent::renderException($exception);
        $status_code = 0;
        if (!isset($response)) {
            if (Yii::$app->has('response')) {
                $status_code = Yii::$app->getResponse()->statusCode;
            }
        } else {
            $status_code = $response->statusCode;
        }
        $this->saveErrorInfo($exception, $status_code);
    }

    protected function saveErrorInfo($exception, $status_code)
    {
        $errorMonitorConfig = new ErrorMonitorConfig();
        $errorLogEnabled = $errorMonitorConfig->errorMonitorEnabled;
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
            $this->sendImmediateNotify($errorMonitorConfig, $errorLog, $status_code);
        }
    }

    protected function sendImmediateNotify($errorMonitorConfig, $errorLog, $status_code)
    {
        $immediateNotifyEnabled = $errorMonitorConfig->immediateNotice;
        if ($immediateNotifyEnabled == 1) {
            $httpCodesForNotify = explode(",", $errorMonitorConfig->httpCodesForImmediateNotify);
            if (strlen($httpCodesForNotify[0]) > 0) {
                if (!in_array($status_code, $httpCodesForNotify)) {
                    return;
                }
            }
            $notifyEmail = $errorMonitorConfig->devmail;
            $validator = new EmailValidator();
            if ($validator->validate($notifyEmail)) {
                $errorUrl = $errorLog->getErrorUrl()->one();
                if ($errorUrl->immediate_notify_count < $errorMonitorConfig->immediateNoticeLimitPerUrl) {
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
