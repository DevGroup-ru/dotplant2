<?= yii\authclient\widgets\AuthChoice::widget([
    'baseAuthUrl' => ['/backend/api/auth'],
    'clientCollection' => 'apiServiceClientCollection',
]) ?>