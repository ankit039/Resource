<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('MUST_LOGIN', true);
include('bootstrap.php');
	
$tpl = new Template('Kies je '.SITE_NAME);

if(isset($_GET['selectAvatar']))
{
	$id = $_GET['selectAvatar'];
	if(!MultiUser::SwitchUser($id))
	{
		$tpl->AddGeneric('head');
		$tpl->AddDefault('issue');
		$tpl->AddGeneric('head-end');
		
		$tpl->AddGeneric('page-issue');
		
		echo $tpl;
		exit;
	}
	
	Core::Location(URL.'/me');
}

$tpl->AddGeneric('head');
$tpl->AddDefault('identity');
$tpl->AddGeneric('head-end');

$date = @date('d-m-Y', Habbo::GetUserData('last_online'));

$current = new Template();
$current->AddParam('look', Habbo::GetUserData('look'));
$current->AddParam('last_alive', $date);
$current->AddGeneric('comp-avatar-current');

$even = '';
$items = '';

foreach($_SESSION['user']['multi'] as $habbo)
{
	if($habbo['id'] == USER_ID)
	{
		continue;
	}
	
	$even = ($even == '') ? 'even' : '';
	$date = @date('d-m-Y', $habbo['last_online']);
	
	$item = new Template();
	$item->AddParam('look', $habbo['look']);
	$item->AddParam('even', $even);
	$item->AddParam('username', $habbo['username']);
	$item->AddParam('last_alive', $date);
	$item->AddParam('id', $habbo['id']);
	$item->AddGeneric('comp-avatar-item');
	
	$items .= $item;
}

$tpl->AddParam('togo', MultiUser::Togo());
$tpl->AddParam('may_add', (MultiUser::Togo() == 0) ? 'false' : 'true');
$tpl->AddParam('current_avatar', (string)$current);
$tpl->AddParam('avatar_items', $items);
$tpl->AddGeneric('page-avatarselect');

echo $tpl;
?>