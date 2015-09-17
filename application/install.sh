#!/bin/bash
/usr/bin/env php ../composer.phar global require "fxp/composer-asset-plugin:~1.0.0"
/usr/bin/env php ../composer.phar install --prefer-dist --optimize-autoloader
