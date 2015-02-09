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
     * @var bool If this response proccessed by DynamicContentTrait
     */
    public $dynamic_content_trait = false;

    /**
     * @var \app\models\DynamicContent matched DynamicContent model from DynamicContentTrait
     */
    public $matched_dynamic_content_trait_model = null;
}