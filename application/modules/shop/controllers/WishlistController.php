<?php

namespace app\modules\shop\controllers;

use yii\web\Controller;
use Yii;
use yii\web\Response;
use app\modules\shop\models\Wishlist;
use yii\web\NotFoundHttpException;

class WishlistController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $wishlists = Wishlist::findByUserId(!Yii::$app->user->isGuest ? Yii::$app->user->id : 0);
        return $this->render('index',
            [
                'wishlists' => $wishlists,
            ]
        );
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionAdd()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $itemId = Yii::$app->request->post('id');

        /** @var Wishlist $wishlist */
        if (0 != Yii::$app->request->post('wishlistId')) {

            if (null !== $wishlist = Wishlist::findById(Yii::$app->request->post('wishlistId'))){
                $wishlist->addToWishlist($itemId);
                return $result[] = ['items' => Wishlist::countItems()];
            }
            throw new NotFoundHttpException();
        } else {
            $wishlists = Wishlist::findByUserId(!Yii::$app->user->isGuest ? Yii::$app->user->id : 0);
            if (empty($wishlists)){
                if (null !== $wishlist = Wishlist::createWishlist(Yii::$app->request->post('title'), true)){
                    $wishlist->addToWishlist($itemId);
                }
                return $result[] = ['items' => Wishlist::countItems()];
            } else {
                if (null !== $wishlist = Wishlist::createWishlist(Yii::$app->request->post('title'), false)){
                    $wishlist->addToWishlist($itemId);
                }
                return $result[] = ['items' => Wishlist::countItems()];
            }
        }
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        if (null !== $wishlist = Wishlist::findById($id)){
            $wishlist->deleteWishlist();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Wishlist removed'));
            return $this->redirect('index');
        }
        throw new NotFoundHttpException();
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDefault($id)
    {
        if (null !== $wishlist = Wishlist::findById($id)){
            $wishlist->setDefaultWishlist();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Default wishlist changed'));
            return $this->redirect('index');
        }
        throw new NotFoundHttpException();
    }


    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionCreate()
    {
        $wishlists = Wishlist::findByUserId(!Yii::$app->user->isGuest ? Yii::$app->user->id : 0);
        if (null !== Wishlist::createWishlist(Yii::$app->request->post('title'), empty($wishlists) ? true : false)){
            Yii::$app->session->setFlash('success', Yii::t('app', 'Wishlist created'));
            return $this->redirect('index');
        }
        throw new NotFoundHttpException();
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRename()
    {
        if (null !== $wishlist = Wishlist::findById(Yii::$app->request->post('id'))){
            $wishlist->renameWishlist(Yii::$app->request->post('title'));
            Yii::$app->session->setFlash('success', Yii::t('app', 'Wishlist created'));
            return $this->redirect('index');
        }
        throw new NotFoundHttpException();
    }

    /**
     * @param $id
     * @param $wishlistId
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRemove($id, $wishlistId)
    {
        if (null !== $wishlist = Wishlist::findById($wishlistId)){
            $wishlist->removeItem($id);
            Yii::$app->session->setFlash('success', Yii::t('app', 'Item removed from the list'));
            return $this->redirect('index');
        }
        throw new NotFoundHttpException();
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionClear($id)
    {
        if (null !== $wishlist = Wishlist::findById($id)){
            if ($wishlist->clearWishlist()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Wishlist cleared'));
                return $this->redirect('index');
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionMove()
    {
        if (null === Yii::$app->request->post('wishlistTo') || null === Yii::$app->request->post('wishlistFrom')) {
            throw new NotFoundHttpException();
        }
        if ((null !== $wishlistFrom = Wishlist::findById(Yii::$app->request->post('wishlistFrom'))) &&
            (null !== $wishlistTo = Wishlist::findById(Yii::$app->request->post('wishlistTo')))) {

            if (null === Yii::$app->request->post('selection')) {
                foreach ($wishlistFrom->items as $item){
                    $wishlistTo->addToWishlist($item->product_id);
                    $wishlistFrom->removeItem($item->product_id);
                }
                Yii::$app->session->setFlash('success', Yii::t('app', 'Items moved'));
                return $this->redirect('index');
            } else {
                foreach ($wishlistFrom->items as $item){
                    if (in_array($item->product_id, Yii::$app->request->post('selection'))) {
                        $wishlistTo->addToWishlist($item->product_id);
                        $wishlistFrom->removeItem($item->product_id);
                    }
                }
                Yii::$app->session->setFlash('success', Yii::t('app', 'Items moved'));
                return $this->redirect('index');
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRemoveGroup()
    {
        if (null === Yii::$app->request->post('wishlistFrom')) {
            throw new NotFoundHttpException();
        }
        if (null !== $wishlist = Wishlist::findById(Yii::$app->request->post('wishlistFrom'))) {

            if (null !== Yii::$app->request->post('selection')) {
                foreach ($wishlist->items as $item){
                    if (in_array($item->product_id, Yii::$app->request->post('selection'))) {
                        $wishlist->removeItem($item->product_id);
                    }
                }
                Yii::$app->session->setFlash('success', Yii::t('app', 'Items removed'));
                return $this->redirect('index');
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionPrice()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (null === Yii::$app->request->post('wishlistId')){
            throw new NotFoundHttpException();
        }
        if (null === Yii::$app->request->post('selections')) {
            $price = Wishlist::getTotalPrice(Yii::$app->request->post('wishlistId'));
            $items = Wishlist::countItems(Yii::$app->request->post('wishlistId'));
        } else {
            $price = Wishlist::getTotalPrice(Yii::$app->request->post('wishlistId'), Yii::$app->request->post('selections'));
            $items = count(Yii::$app->request->post('selections'));
        }
        $ending = '';
        //if($lang == 'ru'){
        if($items%10 > 1 && $items%10 < 5){
            $ending = 'а';
        } elseif($items%10 == 1){
            $ending = '';
        } else {
            $ending = 'ов';
        }
        //}
        return $result = '<span class="wishlist-count">' . $items . '</span> ' . Yii::t('app', 'Item') . $ending . ' ' . Yii::t('app', 'in the amount of') . ' <span class="wishlist-price">' . $price . '</span>';
    }

}
