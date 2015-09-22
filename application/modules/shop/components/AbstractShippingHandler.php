<?php

namespace app\modules\shop\components;

use app\modules\shop\models\Order;
use app\widgets\form\Form;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\base\ViewContextInterface;
use yii\web\View;

abstract class AbstractShippingHandler extends Component implements ViewContextInterface
{
    protected $view;
    protected $lastErrorMessage;

    /**
     * Calculate shipping price
     * @param array $data
     * @return int|false
     */
    abstract public function calculate($data = []);

    /**
     * Get shipping option form for cart
     * @param Form $form
     * @param Order $order
     * @return mixed
     */
    abstract public function getCartForm($form, $order);

//    abstract public function getCartView();

    /**
     * Get last error message
     * @return string
     */
    public function getLastError()
    {
        return $this->lastErrorMessage;
    }

    /**
     * Returns the view object that can be used to render views or view files.
     * The [[render()]] and [[renderFile()]] methods will use
     * this view object to implement the actual view rendering.
     * If not set, it will default to the "view" application component.
     * @return \yii\web\View the view object that can be used to render views or view files.
     */
    public function getView()
    {
        if ($this->view === null) {
            $this->view = Yii::$app->getView();
        }

        return $this->view;
    }

    /**
     * Sets the view object to be used by this widget.
     * @param View $view the view object that can be used to render views or view files.
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * Renders a view.
     * The view to be rendered can be specified in one of the following formats:
     *
     * - path alias (e.g. "@app/views/site/index");
     * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
     *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
     * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
     *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the currently
     *   active module.
     * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
     *
     * If the view name does not contain a file extension, it will use the default one `.php`.
     *
     * @param string $view the view name.
     * @param array $params the parameters (name-value pairs) that should be made available in the view.
     * @return string the rendering result.
     * @throws InvalidParamException if the view file does not exist.
     */
    public function render($view, $params = [])
    {
        return $this->getView()->render($view, $params, $this);
    }

    /**
     * Returns the directory containing the view files for this widget.
     * The default implementation returns the 'views' subdirectory under the directory containing the widget class file.
     * @return string the directory containing the view files for this widget.
     */
    public function getViewPath()
    {
        $class = new \ReflectionClass($this);
        return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
    }
}
