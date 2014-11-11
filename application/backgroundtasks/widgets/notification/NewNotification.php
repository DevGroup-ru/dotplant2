<?php

namespace app\backgroundtasks\widgets\notification;

use yii\base\Widget;

/**
 * Class NewNotification
 * @package app\backgroundtasks\widgets\notification
 *
 * This is Notification widget.
 *
 * @property integer $id
 * @property string $view
 * @property string $label
 * @property string $url
 * @property integer $num
 * @property string $additionClass
 * @property boolean $dark
 * @property string $size
 *
 * @author evgen-d <flynn068@gmail.com>
 */
class NewNotification extends Widget
{

    public $id = 'new-message-count-container';
    public $view = 'new_notification';
    public $url = '/background/notification/only-new-notifications';
    public $num = 0;
    public $additionClass = '';

    public $onSuccess = '';
    public $onError = '';

    private $classes;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->classes = "alert alert-info {$this->additionClass}";
        $this->onSuccess = strlen(trim($this->onSuccess)) > 0 ?
            $this->onSuccess :
            "function (data, container) {
                if(data > 0) {
                    container.fadeIn();
                } else {
                    container.fadeOut();
                }
                container.find('#new-message-count').text(data);
            }";
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $view = $this->getView();
        NotificationAsset::register($view);

        $view->registerJs(
            "jQuery('#{$this->id}').notification(
                '{$this->url}', {$this->onSuccess}".(strlen(trim($this->onError)) > 0 ? ", {$this->onError});" : ");" )
        );
        echo $this->render($this->view, ['num' => $this->num, 'classes' => $this->classes]);
    }
}
