<div class="container">
    <div class="username"><?= Yii::t('app', 'Hello') ?>, <?= $user ?>!</div>
    <h2><?= Yii::t('app', 'New on the site') ?>:</h2>
    <h3><?= $page->h1 ?></h3>
    <div class="content"><?= $page->content ?></div>
    <div class="date"><?= $page->date_added ?></div>
    <hr/>
</div>


<style>
    .container {
        width: 80%;
        border-radius: 5px;
        margin: 5px auto;
    }
    h2 { font-size: 14px; }
    h3 { font-size: 12px; color: grey; font-weight: bold; }
    .username { font-style: italic; font-size: 12px; }
    .content p { text-align: justify; font-size: 14px; text-indent: 35px; margin: 10px 2px; }
    .content h1 { font-size: 14px; }
    .content h2 { font-size: 12px; }
    .date {
        margin-top: 15px;
        float: right;
        font-size: 12px;
        color: grey;
        font-style: italic;
    }
</style>