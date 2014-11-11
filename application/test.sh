#!/bin/bash
cd tests/unit && php yii migrate --interactive=0
cd ../functional && php yii migrate --interactive=0
cd ../acceptance && php yii migrate --interactive=0
cd ../../ && vendor/bin/codecept run
