<?php

namespace app\components;

use Yii;

/**
 * Class Response - extends \yii\web\Response
 * Unifies access to SEO-sensible variables like title, H1, meta.description, etc.
 * Also can force custom view.
 *
 * All rewrites are made in \app\components\Controller::render and filled(if needed) in DynamicContentTrait or ObjectRule
 *
 * @package app\components
 */
class Response extends \yii\web\Response
{
    /**
     * Redefine view with custom view specified by ID.
     * View is redefined only in \app\components\Controller::computeViewFile
     * @var integer|null
     */
    public $view_id = null;

    /**
     * Rewrite title to specified value.
     * In controllers you should still use $this->view->title
     * @var string|null
     */
    public $title = null;

    /**
     * Array of blocks to rewrite(block_name=>block_value).
     * Controllers should use $this->view->blocks and views should use $this->blocks instead of Yii::$app->response->blocks!
     *
     * Standard block list:
     * - h1
     * - announce
     * - content
     *
     * In views use block for standard blocks, not model value!
     * For example: use `$this->blocks['h1']` instead of `$model->h1`.
     *
     * @var array
     */
    public $blocks = [];

    /**
     * Rewrite meta description to specified value.
     * In controllers you should still use $this->view->registerMetaTag
     * @var string|null
     */
    public $meta_description = null;

    /**
     * @var bool If this response was for PrefilteredPage
     */
    public $is_prefiltered_page = false;

    /**
     * @var bool If this response processed by DynamicContentTrait
     */
    public $dynamic_content_trait = false;

    /**
     * @var bool If title was rewrited by DynamicContentTrait
     */
    public $dynamic_content_title_rewrited = false;

    /**
     * @var bool If meta description was rewrited by DynamicContentTrait
     */
    public $dynamic_content_meta_description_rewrited = false;

    /**
     * @var array Array of statuses of blocks which was rewrited
     *
     * can be used like array_keys(Yii::$app->response->dynamic_content_blocks)
     *
     * like this:
     *
     * [
     *     'h1' => true,
     *     'announce' => true
     * ]
     */
    public $dynamic_content_blocks_rewrited = [];

    /**
     * @var \app\models\DynamicContent matched DynamicContent model from DynamicContentTrait
     */
    public $matched_dynamic_content_trait_model = null;

    /**
     * @var bool Is this is the response to backend
     */
    public $is_backend = false;

    /**
     * @property string $globalCacheKey
     * @var string
     */
    protected $_globalCacheKey = null;

    /**
     * @param string $value
     */
    public function setGlobalCacheKey($value)
    {
        $this->_globalCacheKey = $value;
    }

    /**
     * @return string
     */
    public function getGlobalCacheKey()
    {
        $key = $this->_globalCacheKey;
        if (empty($key)) {
            $u = Yii::$app->request->getPathInfo();
            $p = Yii::$app->request->getQueryParams();
            ksort($p);
            $key = $this->_globalCacheKey = implode(':', [
                'ResponseKey',
                $u,
                json_encode($p)
            ]);
        }
        return $key;
    }

    /**
     * @return bool if title was rewrited by DynamicContent
     */
    public function isDynamicTitle()
    {
        return $this->dynamic_content_title_rewrited;
    }

    /**
     * @return bool if meta description was rewrited by DynamicContent
     */
    public function isDynamicMetaDescription()
    {
        return $this->dynamic_content_meta_description_rewrited;
    }

    /**
     * @param string $name of block which should be tested against rewrite
     * @return bool if block with name $name was rewrited by DynamicContent
     */
    public function isDynamicContentBlock($name)
    {
        if (empty($name) || empty($this->dynamic_content_blocks_rewrited[$name])) {
            return false;
        } else {
            return $this->dynamic_content_blocks_rewrited[$name];
        }
    }
}
