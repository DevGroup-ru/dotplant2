<?php
// broken
$I = new ApiGuy($scenario);
$I->wantTo('Get autocomplete for view via json request');
$I->haveHttpHeader('Content-Type','application/x-www-form-urlencoded');
$I->sendPOST('/backend/view/autocomplete', array('q' => ''));
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
//$I->seeResponseContains('{ result: ok}');