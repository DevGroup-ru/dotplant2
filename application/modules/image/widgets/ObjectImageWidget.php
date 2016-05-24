<?php

namespace app\modules\image\widgets;

use app\modules\image\models\Image;
use app\traits\GetImages;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Widget;
use yii\caching\TagDependency;

/**
 * Class ObjectImageWidget
 * @package app\widgets
 */
class ObjectImageWidget extends Widget
{
    /**
     * @var \app\properties\HasProperties|\yii\db\ActiveRecord|GetImages|null
     */
    public $model = null;
    /**
     * @var string
     */
    public $viewFile = 'img';
    /**
     * @var string
     */
    public $noImageViewFile = 'noimage';
    /**
     * @var null|int
     */
    public $limit = null;
    /**
     * @var int
     */
    public $offset = 0;
    /**
     * @var bool
     */
    public $thumbnailOnDemand = false;
    /**
     * @var int
     */
    public $thumbnailWidth = 400;
    /**
     * @var int
     */
    public $thumbnailHeight = 200;
    /**
     * @var bool
     */
    public $useWatermark = false;

    /**
     * @var bool if true and images array empty show "No image"
     */

    public $noImageOnEmptyImages = false;

    /** @var array $additional Additional data passed to view */
    public $additional = [];

    public function run()
    {
        if (is_null($this->model) || empty($this->model->object)) {
            return '';
        }
        $cacheKey = static::className() . ':' . implode(
                "_",
                [
                    $this->model->object->id,
                    $this->model->id,
                    $this->viewFile,
                    $this->limit,
                    $this->offset,
                    $this->thumbnailOnDemand ? '1' : '0',
                    $this->thumbnailWidth,
                    $this->thumbnailHeight,
                    $this->useWatermark,
                ]
            );
        $result = Yii::$app->cache->get($cacheKey);
        if ($result === false) {
            if ($this->offset > 0 || !is_null($this->limit)) {
                $images = $this->model->getImages()->limit($this->limit)->offset($this->offset)->all();
            } else {
                $images = $this->model->images;
            }
            if ($this->noImageOnEmptyImages === true && count($images) === 0) {
                return $this->render(
                    $this->noImageViewFile,
                    [
                        'model' => $this->model,
                        'thumbnailOnDemand' => $this->thumbnailOnDemand,
                        'thumbnailWidth' => $this->thumbnailWidth,
                        'thumbnailHeight' => $this->thumbnailHeight,
                        'useWatermark' => $this->useWatermark,
                        'additional' => $this->additional,
                    ]
                );
            }
            $result = $this->render(
                $this->viewFile,
                [
                    'model' => $this->model,
                    'images' => $images,
                    'thumbnailOnDemand' => $this->thumbnailOnDemand,
                    'thumbnailWidth' => $this->thumbnailWidth,
                    'thumbnailHeight' => $this->thumbnailHeight,
                    'useWatermark' => $this->useWatermark,
                    'additional' => $this->additional,
                ]
            );
            Yii::$app->cache->set(
                $cacheKey,
                $result,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(Image::className()),
                            ActiveRecordHelper::getCommonTag($this->model->className()),
                        ]
                    ]
                )
            );
        }

        return $result;
    }
}
