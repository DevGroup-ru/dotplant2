#!/bin/bash
cd tests/unit && php yii migrate --interactive=0
cd ../../ && vendor/bin/codecept run unit
