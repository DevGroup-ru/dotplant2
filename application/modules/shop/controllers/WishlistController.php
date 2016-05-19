<?php

namespace app\modules\shop\controllers;

use yii\web\Controller;
use Yii;
use yii\web\Response;
use app\modules\shop\models\Wishlist;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use app\modules\shop\models\WishlistProduct;

class WishlistController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $wishlists = Wishlist::getWishlist((!Yii::$app->user->isGuest ? Yii::$app->user->id : 0), Yii::$app->session->get('wishlists', []));
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
        if (true === Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $itemId = Yii::$app->request->post('id');
            $user_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;
            $wishlist_ids = Yii::$app->session->get('wishlists', []);

            /** @var Wishlist $wishlist */
            if (0 != Yii::$app->request->post('wishlistId')) {

                if (null !== $wishlist = Wishlist::findOne([
                        'id' => Yii::$app->request->post('wishlistId'),
                        'user_id' => $user_id,
                    ])
                ) {
                    $wishlist->addToWishlist($itemId);
                    return $result[] = [
                        'items' => Wishlist::countItems($user_id, $wishlist_ids),
                        'isSuccess' => true,
                    ];
                }
                return $result[] = [
                    'items' => Wishlist::countItems($user_id, $wishlist_ids),
                    'errorMessage' => Yii::t('app', 'Failed to add items'),
                    'isSuccess' => false,
                ];
            } else {
                $wishlists = Wishlist::getWishlist($user_id, $wishlist_ids);
                if (empty($wishlists)) {
                    if (null !== $wishlist = Wishlist::createWishlist(Yii::$app->request->post('title'), $user_id, $wishlist_ids, true)) {
                        $wishlist->addToWishlist($itemId);
                    }
                    return $result[] = [
                        'items' => Wishlist::countItems($user_id, $wishlist_ids),
                        'isSuccess' => true,
                    ];
                } else {
                    if (null !== $wishlist = Wishlist::createWishlist(Yii::$app->request->post('title'), $user_id, $wishlist_ids, false)) {
                        $wishlist->addToWishlist($itemId);
                    }
                    return $result[] = [
                        'items' => Wishlist::countItems($user_id, $wishlist_ids),
                        'isSuccess' => true,
                    ];
                }
            }
        }
        throw new NotFoundHttpException();
    }

    /**
     * @param $id
     * @return Response
     */
    public function actionDelete($id)
    {
        /** @var Wishlist $wishlist */
        if (null !== $wishlist = Wishlist::findOne([
                'id' => $id,
                'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
            ])
        ) {
            $wishlist->delete();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Wishlist has been removed'));
            return $this->redirect('index');
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'Failed to remove wishlist'));
        return $this->redirect('index');
    }

    /**
     * @param $id
     * @return Response
     */
    public function actionDefault($id)
    {
        if (Wishlist::setDefaultWishlist($id, (!Yii::$app->user->isGuest ? Yii::$app->user->id : 0), Yii::$app->session->get('wishlists', []))) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Default wishlist changed'));
            return $this->redirect('index');
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'Failed to set default wishlist'));
        return $this->redirect('index');
    }


    /**
     * @return Response
     */
    public function actionCreate()
    {
        $wishlists = Wishlist::getWishlist((!Yii::$app->user->isGuest ? Yii::$app->user->id : 0), Yii::$app->session->get('wishlists', []));
        if (null !== Wishlist::createWishlist(
                Yii::$app->request->post('title'),
                !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
                Yii::$app->session->get('wishlists', []),
                empty($wishlists) ? true : false)
        ) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Wishlist has been created'));
            return $this->redirect('index');
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'Failed to create wishlist'));
        return $this->redirect('index');
    }

    /**
     * @return Response
     */
    public function actionRename()
    {
        /** @var Wishlist $wishlist */
        if (null !== $wishlist = Wishlist::findOne([
                'id' => Yii::$app->request->post('id'),
                'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
            ])
        ) {
            $wishlist->renameWishlist(Yii::$app->request->post('title'));
            Yii::$app->session->setFlash('success', Yii::t('app', 'Wishlist has been renamed'));
            return $this->redirect('index');
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'Failed to rename wishlist'));
        return $this->redirect('index');
    }

    /**
     * @param $id
     * @param $wishlistId
     * @return Response
     */
    public function actionRemove($id, $wishlistId)
    {
        /** @var WishlistProduct $item */
        if (null !== $item = WishlistProduct::findOne([
                'wishlist_id' => $wishlistId,
                'product_id' => $id
            ])
        ) {
            $item->delete();
            Yii::$app->session->setFlash('success', Yii::t('app', 'Item removed from the list'));
            return $this->redirect('index');
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'Failed to remove item'));
        return $this->redirect('index');
    }

    /**
     * @param $id
     * @return Response
     */
    public function actionClear($id)
    {
        /** @var Wishlist $wishlist */
        if (null !== $wishlist = Wishlist::findOne([
                'id' => $id,
                'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
            ])
        ) {
            if ($wishlist->clearWishlist()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Wishlist has been cleared'));
                return $this->redirect('index');
            }
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'Failed to clear wishlist'));
        return $this->redirect('index');
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionMove()
    {
        $wishlistToId = Yii::$app->request->post('wishlistTo');
        $wishlistFromId = Yii::$app->request->post('wishlistFrom');
        $selections = Yii::$app->request->post('selection');
        $user_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;

        if (null === $wishlistToId || null === $wishlistFromId) {
            throw new BadRequestHttpException;
        }
        /** @var Wishlist $wishlistFrom */
        /** @var Wishlist $wishlistTo */
        if ((null !== $wishlistFrom = Wishlist::findOne([
                    'id' => $wishlistFromId,
                    'user_id' => $user_id,
                ])) &&
            (null !== $wishlistTo = Wishlist::findOne([
                    'id' => $wishlistToId,
                    'user_id' => $user_id,
                ]))
        ) {

            if (null === $selections) {
                foreach ($wishlistFrom->items as $item) {
                    $wishlistTo->addToWishlist($item->product_id);
                    $wishlistFrom->removeItem($item->product_id);
                }
            } else {
                foreach ($wishlistFrom->items as $item) {
                    if (in_array($item->product_id, $selections)) {
                        $wishlistTo->addToWishlist($item->product_id);
                        $wishlistFrom->removeItem($item->product_id);
                    }
                }
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Items moved'));
            return $this->redirect('index');
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'Failed to move items'));
        return $this->redirect('index');
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionRemoveGroup()
    {
        $wishlistFromId = Yii::$app->request->post('wishlistFrom');
        if (null === $wishlistFromId) {
            throw new BadRequestHttpException;
        }
        /** @var Wishlist $wishlist */
        if (null !== $wishlist = Wishlist::findOne([
                'id' => $wishlistFromId,
                'user_id' => !Yii::$app->user->isGuest ? Yii::$app->user->id : 0,
            ])
        ) {

            if (null !== $selections = Yii::$app->request->post('selection')) {
                foreach ($wishlist->items as $item) {
                    if (in_array($item->product_id, $selections)) {
                        $wishlist->removeItem($item->product_id);
                    }
                }
                Yii::$app->session->setFlash('success', Yii::t('app', 'Items removed'));
                return $this->redirect('index');
            }
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'Failed to move items'));
        return $this->redirect('index');
    }

    /**
     * @return string
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionPrice()
    {
        if (true === Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $selections = Yii::$app->request->post('selections');
            $wishlistId = Yii::$app->request->post('wishlistId');
            $user_id = !Yii::$app->user->isGuest ? Yii::$app->user->id : 0;
            $wishlist_ids = Yii::$app->session->get('wishlists', []);
            if (null === $wishlistId) {
                throw new BadRequestHttpException;
            }
            if (null === $selections) {
                $price = Wishlist::getTotalPrice($user_id, $wishlist_ids, $wishlistId);
                $items = Wishlist::countItems($user_id, $wishlist_ids, $wishlistId);
            } else {
                $price = Wishlist::getTotalPrice($user_id, $wishlist_ids, $wishlistId, $selections);
                $items = count($selections);
            }
            return $result = '<span class="wishlist-count">' . $items . '</span> ' . Yii::t('app', '{n, plural, one{item} other{items}} in the amount of', ['n' => $items]) . ' <span class="wishlist-price">' . $price . '</span>';
        }
        throw new NotFoundHttpException();
    }
}
