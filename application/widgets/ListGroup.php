<?php

namespace app\widgets;

use yii\base\InvalidConfigException;
use yii\bootstrap\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class ListGroup extends Widget
{
    public $items = [];
    public $encode = true;

    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        parent::init();
        Html::addCssClass($this->options, 'list-group');
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo $this->renderItems($this->items);
    }

    /**
     * Renders menu items.
     * @param  array                  $items the menu items to be rendered
     * @return string                 the rendering result.
     * @throws InvalidConfigException if the label option is not specified in one of the items.
     */
    protected function renderItems($items)
    {
        $lines = [];
        foreach ($items as $i => $item) {
            if (isset($item['visible']) && !$item['visible']) {
                unset($items[$i]);
                continue;
            }
            if (is_string($item)) {
                $lines[] = $item;
                continue;
            }
            if (!isset($item['title'])) {
                throw new InvalidConfigException("The 'title' option is required.");
            }
            $title = $this->encode ? Html::encode($item['title']) : $item['title'];
            $titleOptions = ArrayHelper::getValue($item, 'titleOptions', []);
            Html::addCssClass($titleOptions, 'list-group-item-heading');
            $titleCode = Html::tag('h4', $title, $titleOptions);

            $description = $this->encode ? Html::encode($item['description']) : $item['description'];
            $descriptionOptions = ArrayHelper::getValue($item, 'descriptionOptions', []);
            Html::addCssClass($descriptionOptions, 'list-group-item-text');
            $descriptionCode = Html::tag('p', $description, $descriptionOptions);

            $linkOptions = ArrayHelper::getValue($item, 'linkOptions', []);
            Html::addCssClass($linkOptions, 'list-group-item');
            Html::addCssStyle($linkOptions, 'word-wrap: break-word');
            if (isset($item['active']) && $item['active']) {
                Html::addCssClass($linkOptions, 'active');
            }
            $linkOptions['tabindex'] = '-1';
            $lines[] = Html::a(
                $titleCode."\n".$descriptionCode,
                ArrayHelper::getValue($item, 'url', '#'),
                $linkOptions
            );
        }
        return Html::tag('div', implode("\n", $lines), $this->options);
    }
}
