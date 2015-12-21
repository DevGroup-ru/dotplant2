<?php
/**
 * this Action provides group publish and unpublish functionality.
 * Usage example: in backend controller
 * public function actions()
 * {
 *  return [
 *  ...
 *      'publish-switch' => [
 *          'class' => app\backend\actions\MassPublishAction::className(),
 *          'modelName' => Page::className(),
 *          'attribute' => 'published',
 *      ]
 * ];
 * @property string $modelName must be name of existing Model class. This property is required
 * @property string $attribute name of model attribute that provides public item visibility
 */

namespace app\backend\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use app\backend\widgets\PublishSwitchButtons;
use yii\web\ServerErrorHttpException;

class MassPublishAction extends Action
{
    public $modelName = '';
    public $attribute = '';

    /**
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        parent::init();
        if (false === is_subclass_of($this->modelName, '\yii\db\ActiveRecord')) {
            throw new ServerErrorHttpException('Model class does not exists');
        }
        if (false === in_array($this->attribute, (new $this->modelName)->attributes())) {
            throw new ServerErrorHttpException("Model '{$this->modelName}' has no '{$this->attribute}' field'" );
        }
    }

    /**
     * @return int
     * @throws NotFoundHttpException
     */
    public function run()
    {
        if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $items = is_array(Yii::$app->request->post('ps-items')) ? Yii::$app->request->post('ps-items') : [];
        $mode = Yii::$app->request->post('ps-action');
        $active = null;
        $message = '';
        switch ($mode) {
            case PublishSwitchButtons::MASS_PUBLISH :
                $message = 'Items published: {n}';
                $active = 1;
                break;
            case PublishSwitchButtons::MASS_UNPUBLISH :
                $message = 'Items unpublished: {n}';
                $active = 0;
                break;
        }
        if (false === empty($items) && null !== $active) {
            $class = $this->modelName;
            $num = $class::updateAll([$this->attribute => $active],['id' => $items]);
            Yii::$app->session->setFlash('info', Yii::t('app', $message, ['n' => $num]));
            return 1;
        }
        return 0;
    }
}