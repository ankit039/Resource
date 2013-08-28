<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

include('bootstrap.php');

if(!isset($_GET['ok']))
{
	$token = $_SESSION['user']['token'];
	
	Habbo::Logout();
	Core::Location(URL.'/account/logout_ok?token='.$token);
}

$tpl = new Template('Uitgelogd');
$tpl->AddGeneric('page-logout');

echo $tpl;
?>