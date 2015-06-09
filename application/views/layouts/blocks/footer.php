<?php
use \app\extensions\DefaultTheme\models\ThemeParts;
?>
<?= ThemeParts::renderPart('pre-footer') ?>
<?= ThemeParts::renderPart('footer') ?>
<?= ThemeParts::renderPart('post-footer') ?>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>