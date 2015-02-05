#!/bin/bash
chmod 777 ./runtime/
chmod 777 ./web/assets/
chmod 777 ./web/upload/
chown www-data:www-data ./config/email-config.php
./update-dependencies.sh