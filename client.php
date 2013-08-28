<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('MUST_LOGIN', true);
include('bootstrap.php');

$tpl = new Template('Client');
$tpl->AddGeneric('head');
$tpl->AddLine(new IncludeFile('{webbuild}/static/js/swfobject.js'));
$tpl->AddGeneric('head-client');
$tpl->AddGeneric('head-end');

$result = Core::$DB->query('SELECT * FROM site_client_settings')->fetch_assoc();
foreach($result as $key => $value)
{
	$tpl->AddParam($key, $value);
}

$ssoticket[] = 'SSO';
$ssoticket[] = Core::Hash(rand(0, 99999) . time());
$ssoticket[] = rand(0, 5);
$ssoticket = implode('-', $ssoticket);

Habbo::SetUserData('auth_ticket', $ssoticket);
Core::$DB->prepare('UPDATE users SET auth_ticket = ? WHERE id = ? LIMIT 1')->bind_param($ssoticket, USER_ID)->execute();

$tpl->AddParam('auth_ticket', $ssoticket);
$tpl->AddGeneric('page-client');

echo $tpl;
?>