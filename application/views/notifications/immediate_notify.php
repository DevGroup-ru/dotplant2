<h1><?= Yii::t('app', 'Immediate error report (sent {0})', date(DateTime::RFC1123, time())) ?> </h1>

<table>
    <tr>
        <td>Url</td>
        <td><?= $info['url'] ?></td>
    </tr>
    <tr>
        <td>Message</td>
        <td><?= $info['message'] ?></td>
    </tr>
    <tr>
        <td>HTTP code</td>
        <td><?= $info['http_code'] ?></td>
    </tr>
    <tr>
        <td>Request variables</td>
        <td><?= $info['reuest_vars'] ?></td>
    </tr>
    <tr>
        <td>Server variables</td>
        <td><?= $info['server_vars'] ?></td>
    </tr>
</table>