<?php

$I = new TestGuy($scenario);
$I->wantTo('ensure that home page works');
$I->amOnPage(Yii::$app->homeUrl);
$I->see('dotplant.ru');
//! @todo FIX THIS TEST
// $I->seeLink('About');
// $I->click('About');
// $I->see('This is the About page.');
