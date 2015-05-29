<?php

ob_start();
system('git pull');
$output = ob_get_contents();
ob_end_clean();


if (!preg_match('/up-to-date/',$output)){

	$config = include __DIR__ . '/../framework/config/application.php';

	$robot = $config->app->name;
	$site = $config->app->product;

	$curl = 'curl -X POST -d "{\"from\": \"Robot ('.$robot.')\", \"color\": \"green\", \"renderer\": \"markdown\", \"text\": \"Сайт (http://'.$site.') обновлен!\" }" https://api.kato.im/rooms/E86048F31D5753409159EAD2AAA4F63B886D8B8D888026D13FFF56C849AE07CB/simple';
	// echo "$curl";
	system($curl);

}