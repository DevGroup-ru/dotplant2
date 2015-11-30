<?php


namespace app\modules\user\actions;


use app\modules\user\models\UserActivity;
use Yii;
use yii\base\Action;
use yii\web\Response;

class HeartbeatAction extends Action
{
    public function init()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest || is_null($this->modelId) || is_null($this->objectId)) {
            return ['activityList' => [], 'is_main' => true];
        }
    }

    public function run()
    {
        $userId = Yii::$app->user->id;
        $query = UserActivity::find()->where(
            ['object_id' => $this->objectId, 'object_model_id' => $this->modelId]
        )->andWhere(
            ['not', ['user_id' => $userId]]
        );
        $mainCount = $query->andWhere(['is_main' => 1])->count();
        $usersList = $query->all();
        \yii\helpers\Url::to('/'.Yii::$app->requestedAction->controller->module->id . '/' . Yii::$app->requestedAction->controller->id.'/heartbeat');
        return ['activityList' => [], 'is_main' => true];
    }
}