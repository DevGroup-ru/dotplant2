<?php
/**
 * @var \yii\web\View $this
 * @var \app\backend\models\OrderChat[] $list
 * @var \app\modules\shop\models\Order $order
 */
use yii\helpers\Html;

$this->registerAssetBundle(\app\backend\assets\KoAsset::className());

    $list = array_reduce($list,
        function ($result, $item)
        {
            /** @var \app\backend\models\OrderChat $item */
            $user = $item->user;
            $result[] = [
                'message' => $item->message,
                'user' => null !== $user ? $user->username : Yii::t('app', 'Unknown'),
                'gravatar' => null !== $user ? $user->gravatar() : null,
                'date' => $item->date,
            ];
            return $result;
        }, []
    );

?>
<div class="widget-body widget-hide-overflow no-padding">
    <div id="chat-body" class="chat-body custom-scroll">
        <ul data-bind="template: {name: 'koTplMessage', foreach: listMessages}">
        </ul>
    </div>
    <div class="chat-footer">
        <div class="textarea-div">
            <div class="typearea">
                <textarea class="custom-scroll" data-bind="textInput: newMessage"></textarea>
            </div>
        </div>
        <span class="textarea-controls">
            <button class="btn btn-sm btn-primary pull-right" data-bind="click: clickSendMessage"><?= \Yii::t('app', 'Submit'); ?></button>
        </span>
    </div>
</div>

<script type="text/html" id="koTplMessage">
    <li class="message">
        <img class="online" alt="" data-bind="attr: {src: gravatar}">
        <div class="message-text">
            <time data-bind="text: date"></time>
            <a href="javascript:void(0);" class="username" data-bind="text: user"></a>
            <!--ko text: message--><!--/ko-->
        </div>
    </li>
</script>

<?php ob_start(); ?>
    (function(){
        var dataMessages = '<?= \yii\helpers\Json::encode(array_values($list)); ?>';

        function modelMessage(message, user, gravatar, date) {
            this.message = message;
            this.user = user;
            this.gravatar = gravatar;
            this.date = date;
        }

        function OrderChat() {
            var self = this;
            self.newMessage = ko.observable();
            self.clickSendMessage = function(data, event) {
                event.preventDefault();
                var $_message = self.newMessage();
                jQuery.post(
                    '<?= \yii\helpers\Url::toRoute(['/shop/backend-order/send-to-order-chat', 'orderId' => $order->id], true) ?>',
                    {
                        message: $_message
                    },
                    function (data) {
                        if (1 == data.status) {
                            self.newMessage('');
                            console.log(data);
                            self.listMessages.unshift(
                                new modelMessage(data.message, data.user, data.gravatar, data.date)
                            );
                        }
                    },
                    'json'
                );
                return false;
            }
        }

        OrderChat.prototype.listMessages = ko.observableArray(ko.utils.arrayMap(ko.utils.parseJson(dataMessages), function(item) {
            return new modelMessage(item.message, item.user, item.gravatar, item.date);
        }));

        ko.applyBindings(new OrderChat());
    })();
<?php $this->registerJs(ob_get_clean(), \yii\web\View::POS_END); ?>