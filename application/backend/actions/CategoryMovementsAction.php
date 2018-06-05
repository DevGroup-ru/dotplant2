<?php

namespace app\backend\actions;


use app\models\BaseObject;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\base\Action;
use Yii;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\backend\widgets\CategoryMovementsButtons;

class CategoryMovementsAction extends Action
{
    private $categoryId;
    private $items = [];
    private $action;
    /** @var  BaseObject */
    private static $object;

    /**
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     */
    public function init()
    {
        if (false === Yii::$app->request->isAjax) {
            throw new NotFoundHttpException('Page not found');
        }
        $catId = Yii::$app->request->post('cat-id');
        if (null !== Category::findOne(['id' => $catId])) {
            $this->categoryId = $catId;
        } else {
            throw new ServerErrorHttpException("Can't find Category with id {$catId}");
        }
        if (true === empty(static::$object)) {
            $product = Yii::$container->get(Product::class);
            static::$object = BaseObject::getForClass(get_class($product));
        }
        $this->action = Yii::$app->request->post('action', '');
        $this->items = Yii::$app->request->post('mc-items', []);
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $n = 0;
        switch($this->action) {
            case CategoryMovementsButtons::ADD_ACTION :
                $n = $this->add();
                break;
            case CategoryMovementsButtons::MOVE_ACTION :
                $n = $this->move();
                break;
        }
        $tags = [];
        foreach ($this->items as $id) {
            $product = Yii::$container->get(Product::class);
            $tags[] = ActiveRecordHelper::getObjectTag(get_class($product), $id);
        }
        TagDependency::invalidate(Yii::$app->cache, $tags);
        Yii::$app->session->setFlash('info', Yii::t('app', 'Items updated: {n}', ['n' => $n]));
    }

    /**
     * @return int
     */
    private function move()
    {
        $this->add();
        $product = Yii::$container->get(Product::class);
        return $product::updateAll(['main_category_id' => $this->categoryId],['id' => $this->items]);
    }

    /**
     * @return int
     * @throws \yii\db\Exception
     */
    private function add()
    {
        $exists = (new Query())->select('object_model_id')
            ->from(static::$object->categories_table_name)
            ->where([
                'category_id' => $this->categoryId,
                'object_model_id' => $this->items
            ])
            ->column();
        $new = array_diff($this->items, $exists);
        $rows = [];
        foreach ($new as $id) {
            $rows[] = [$this->categoryId, $id, 0];
        }
        $n = 0;
        if (false === empty($rows)) {
            $n = Yii::$app->db->createCommand()->batchInsert(
                static::$object->categories_table_name,
                ['category_id', 'object_model_id', 'sort_order'],
                $rows
            )->execute();
        }
        return $n;
    }
}