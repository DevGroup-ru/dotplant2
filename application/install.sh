#!/bin/bash
/usr/bin/env php ../composer.phar global require "fxp/composer-asset-plugin:~1.1.0"
/usr/bin/env php ../composer.phar install --prefer-dist --optimize-autoloader
./yii stubs config/web.php config/console.php
