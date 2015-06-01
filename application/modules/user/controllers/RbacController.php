<?php

namespace app\modules\user\controllers;

use app\backend\components\BackendController;
use app\backend\models\AuthItemForm;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;
use yii\helpers\Url;
use Yii;

/**
 * BackendUserController implements the CRUD actions for User model.
 */
class RbacController extends BackendController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['user manage'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'remove-items' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all AuthItems models.
     * @return mixed
     */
    public function actionIndex()
    {
        $rules = \Yii::$app->getAuthManager()->getRules();
        $permissions = new ArrayDataProvider(
            [
                'id' => 'permissions',
                'allModels' => \Yii::$app->getAuthManager()->getPermissions(),
                'sort' => [
                    'attributes' => ['name', 'description', 'ruleName', 'createdAt', 'updatedAt'],
                ],
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        $roles = new ArrayDataProvider(
            [
                'id' => 'roles',
                'allModels' => \Yii::$app->getAuthManager()->getRoles(),
                'sort' => [
                    'attributes' => ['name', 'description', 'ruleName', 'createdAt', 'updatedAt'],
                ],
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        return $this->render(
            'index',
            [
                'permissions' => $permissions,
                'roles' => $roles,
                'isRules' => !empty($rules),
            ]
        );
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param $type
     * @return string|\yii\web\Response
     * @throws \InvalidArgumentException
     */
    public function actionCreate($type)
    {
        $rules = ArrayHelper::map(\Yii::$app->getAuthManager()->getRules(), 'name', 'name');
        $model = new AuthItemForm(['isNewRecord' => true]);
        if ($model->load($_POST) && $model->validate()) {
            $item = $model->createItem();
            if (strlen($model->getErrorMessage()) > 0) {
                \Yii::$app->getSession()->setFlash('error', $model->getErrorMessage());
                return $this->redirect(['update', 'id' => $item->name, 'type' => $item->type]);
            } else {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/user/rbac/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/user/rbac/create',
                                'type' => $type,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/user/rbac/update',
                                    'id' => $item->name,
                                    'type' => $type,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            }
        } else {
            switch ($type) {
                case Item::TYPE_PERMISSION:
                    $model->type = Item::TYPE_PERMISSION;
                    $items = ArrayHelper::map(
                        \Yii::$app->getAuthManager()->getPermissions(),
                        'name',
                        function ($item) {
                            return $item->name.(strlen($item->description) > 0 ? ' ['.$item->description.']' : '');
                        }
                    );
                    break;
                case Item::TYPE_ROLE:
                    $model->type = Item::TYPE_ROLE;
                    $items = ArrayHelper::map(
                        ArrayHelper::merge(
                            \Yii::$app->getAuthManager()->getPermissions(),
                            \Yii::$app->getAuthManager()->getRoles()
                        ),
                        'name',
                        function ($item) {
                            return $item->name.(strlen($item->description) > 0 ? ' ['.$item->description.']' : '');
                        },
                        function ($item) {
                            return \Yii::$app->params['rbacType'][$item->type];
                        }
                    );
                    break;
                default:
                    throw new \InvalidArgumentException('Unexpected item type');
            }
            return $this->render(
                'update',
                [
                    'model' => $model,
                    'rules' => $rules,
                    'items' => $items,
                    'children' => [],
                    'isNewRecord' => true,
                ]
            );
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param $id
     * @param $type
     * @return string|\yii\web\Response
     * @throws \InvalidArgumentException
     */
    public function actionUpdate($id, $type)
    {
        $rules = ArrayHelper::map(\Yii::$app->getAuthManager()->getRules(), 'name', 'name');
        $model = new AuthItemForm;
        if ($model->load($_POST) && $model->validate()) {
            $item = $model->updateItem();
            if (strlen($model->getErrorMessage()) > 0) {
                \Yii::$app->getSession()->setFlash('error', $model->getErrorMessage());
                return $this->redirect(['update', 'id' => $item->name, 'type' => $item->type]);
            } else {
                $returnUrl = Yii::$app->request->get('returnUrl', ['/user/rbac/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/user/rbac/create',
                                'type' => $type,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/user/rbac/update',
                                    'id' => $item->name,
                                    'type' => $type,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            }
        } else {
            switch ($type) {
                case Item::TYPE_PERMISSION:
                    $item = \Yii::$app->getAuthManager()->getPermission($id);
                    $items = ArrayHelper::map(
                        \Yii::$app->getAuthManager()->getPermissions(),
                        'name',
                        function ($item) {
                            return $item->name.(strlen($item->description) > 0 ? ' ['.$item->description.']' : '');
                        }
                    );
                    break;
                case Item::TYPE_ROLE:
                    $item = \Yii::$app->getAuthManager()->getRole($id);
                    $items = ArrayHelper::map(
                        ArrayHelper::merge(
                            \Yii::$app->getAuthManager()->getPermissions(),
                            \Yii::$app->getAuthManager()->getRoles()
                        ),
                        'name',
                        function ($item) {
                            return $item->name.(strlen($item->description) > 0 ? ' ['.$item->description.']' : '');
                        },
                        function ($item) {
                            return \Yii::$app->params['rbacType'][$item->type];
                        }
                    );
                    break;
                default:
                    throw new \InvalidArgumentException('Unexpected item type');
            }
            $children = \Yii::$app->getAuthManager()->getChildren($id);
            $selected = [];
            foreach ($children as $child) {
                $selected[] = $child->name;
            }
            $model->name = $item->name;
            $model->oldname = $item->name;
            $model->type = $item->type;
            $model->description = $item->description;
            $model->ruleName = $item->ruleName;
            return $this->render(
                'update',
                [
                    'model' => $model,
                    'rules' => $rules,
                    'children' => $selected,
                    'items' => $items,
                ]
            );
        }
    }

    public function actionRemoveItems()
    {
        foreach ($_POST['items'] as $item) {
            \Yii::$app->getAuthManager()->remove(new Item(['name' => $item]));
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        \Yii::$app->getAuthManager()->remove(new Item(['name' => $id]));
        return $this->redirect(['rbac/']);
    }
}
