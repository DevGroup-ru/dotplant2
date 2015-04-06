#!/bin/bash
mkdir -p web/assets/{js,css}
./yii asset/compress assets.php config/assets-prod.php
