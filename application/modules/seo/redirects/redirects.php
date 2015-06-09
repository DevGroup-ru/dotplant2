<?php

$additional_redirects = [
    'static' => [
        //
    ],
    'regular' => [
        //
    ],
];

$redirects = array_merge_recursive(
    file_exists(__DIR__ . '/redirectsArray.php') ? require(__DIR__ . '/redirectsArray.php') : [],
    $additional_redirects
);

if (isset($redirects['static'][$_SERVER['REQUEST_URI']])) {
	$host = '';
	if (substr($redirects['static'][$_SERVER['REQUEST_URI']], 0, 1) == "/") {
        $host .= isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'], 'on') === 0 || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') === 0 ? 'https://' : 'http://';
        $host .= $_SERVER['SERVER_NAME'];
	}
    header('Location: '.$host.$redirects['static'][$_SERVER['REQUEST_URI']], true, 301);
    exit();
} elseif (isset($redirects['regular'])) {
	foreach ($redirects['regular'] as $rule => $redirect){
		if (preg_match($rule, $_SERVER['REQUEST_URI'])){
    		header("Location: ".preg_replace($rule, $redirect, $_SERVER['REQUEST_URI']), true, 301);
    		exit();
		}
	}
}