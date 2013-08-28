<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('IN_MAINTENANCE', true);
include('bootstrap.php');
 
$tpl = new Template('Onderhoud!');
$tpl->AddParam('twitter_username', Core::$Config['site']['twitter']);
$tpl->AddGeneric('page-maintenance');
	
echo $tpl;
?>