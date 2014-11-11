<?php

/**
 * @var int $num
 */

?>

<div id="new-message-count-container" current="<?= time(); ?>" class="alert alert-info" style="display: none;">
    <a class="alert-link" href="/background/notification/index">You have <span id="new-message-count"><?= $num; ?></span> new notification(s)</a>
</div>