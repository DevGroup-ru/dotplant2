<?php


namespace app\modules\user\actions;


use app\modules\user\models\UserActivity;
use Yii;
use yii\base\Action;
use yii\helpers\Url;
use yii\web\Response;

class HeartbeatAction extends Action
{
    public $objectId = null;

    public function run()
    {
        $action = Yii::$app->request->post('action', 'default');
        $userId = Yii::$app->user->id;
        $modelId = Yii::$app->request->post('modelId');
        $currentActivity = UserActivity::findOne(
            ['object_id' => $this->objectId, 'object_model_id' => $modelId, 'user_id' => $userId]
        );


        $returnValue = ['activityList' => [], 'is_main' => true];

        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->user->isGuest || is_null(Yii::$app->request->post('modelId', null)) || is_null(
                $this->objectId
            )
        ) {
            return $returnValue;
        }


        $query = UserActivity::find()->where(
            ['object_id' => $this->objectId, 'object_model_id' => $modelId]
        )->andWhere(
            ['not', ['user_id' => $userId]]
        );
        $mainCount = $query->andWhere(['is_main' => 1])->count();
        $usersList = $query->all();

        $returnValue['activityList'] = $usersList;//xurma
        if ($mainCount != 0) {
            $returnValue['is_main'] = false;
        }
        if (is_null($currentActivity)) {
            $currentActivity = new UserActivity();
            $currentActivity->setAttributes(
                ['object_id' => $this->objectId, 'object_model_id' => $modelId, 'user_id' => $userId]
            );
            $currentActivity->is_main = (int) $returnValue['is_main'];
            $currentActivity->save();
        } else {
            switch ($action) {
                case 'close':
                    $currentActivity->delete();
                    break;
                default:
                    $currentActivity->save();
            }
        }
        return $returnValue;
    }
}