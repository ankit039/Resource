<?php

include('../bootstrap.php');

if($_GET['figure'] == USER_NAME)
{
	$_GET['figure'] = Habbo::GetUserData('look');
}

$file = http_build_query($_GET);
$name = SITE_NAME.'-'.Core::Hash($file);
$path = ROOT.'habbo-imaging'.DS.'avatarimage'.DS.$name.'.png';

if(!file_exists($path))
{
	file_put_contents($path, file_get_contents('http://www.habbo.nl/habbo-imaging/avatarimage?'.$file)); 
}

header('Content-Type: image/png');
die(file_get_contents($path));
?>