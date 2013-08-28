<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('PAGE', 1);
define('PARRENT', 6);
define('MUST_LOGIN', true);
include('bootstrap.php');
	
$tab = 1;
if(isset($_GET['tab']))
{
	$tab = $_GET['tab'];
}
	
$tpl = new Template('Mijn gegevens');
	
$tpl->AddParam('is_message', false);
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$motto = $_POST['motto'];
	$allow = (isset($_POST['friendRequestsAllowed'])) ? 0 : 1;
	$online = $_POST['showOnlineStatus'];
	$follow = $_POST['followFriendMode'];
	$trade = $_POST['tradingsetting'];
	
	Core::$DB->prepare('UPDATE `users` SET `motto` = "'.$motto.'", `block_newfriends` = "'.$allow.'", `hide_online` = "'.$online.'", `hide_inroom` = "'.$follow.'", `block_trade` = "'.$trade.'" WHERE `id` = "'.USER_ID.'" LIMIT 1')->execute();
			
	Habbo::SetUserData('motto', Core::RealString($motto), USER_ID, true);
	Habbo::SetUserData('block_newfriends', $allow, USER_ID, true);
	Habbo::SetUserData('hide_online', $online, USER_ID, true);
	Habbo::SetUserData('hide_inroom', $follow, USER_ID, true);
	Habbo::SetUserData('block_trade', $trade, USER_ID, true);
	
	new UserInfoMe(); 
	UserInfoMe::DeleteCache();
	
	$tpl->AddParam('is_message', true);
	$tpl->AddParam('message_type', 'green');
	$tpl->AddParam('message', 'Jeuh! Gegevens bijgewerkt!');
}

$tpl->AddGeneric('head');
$tpl->AddDefault();
$tpl->AddLine(new IncludeFile('{webbuild}/static/js/settings.js'));
$tpl->AddLine(new IncludeFile('{webbuild}/static/styles/settings.css'));
$tpl->AddGeneric('head-end');
	
$tpl->AddParam('body_id', 'home');
new CommunityMenu();
$tpl->AddTemplate(CommunityMenu::GetTemplate());
	
$tpl->AddLine('<div><div class="content">');
	
$menu = new Template();
$menu->AddParam('sel_preferences', ($tab == 1) ? 'class="selected"' : '');
$menu->AddParam('sel_mail', ($tab == 2) ? 'class="selected"' : '');
$menu->AddParam('sel_password', ($tab == 3) ? 'class="selected"' : '');
$menu->AddParam('sel_friendlist', ($tab == 4) ? 'class="selected"' : '');
$menu->AddParam('can_feed', false);
$menu->AddParam('no_club', false);
$menu->AddGeneric('comp-settings-menu');
$tpl->AddTemplate($menu);

$_SESSION['app_key'] = $key = uniqid();
	
$page = new Template();
$page->AddParam('app_key', $key);
switch($tab)
{
	default:
	case '1':
		$visible = 1;
		$page->AddParam('motto', Habbo::GetUserData('motto'));
		$page->AddParam('page-visible', ($visible == 1) ? 'checked' : '');
		$page->AddParam('page-invisible', ($visible == 0) ? 'checked' : '');
		$page->AddParam('friend-allow', (Habbo::GetUserData('block_newfriends') == 0) ? 'checked' : '');
		$page->AddParam('online-nobody', (Habbo::GetUserData('hide_online') == 0) ? 'checked' : '');
		$page->AddParam('online-everybody', (Habbo::GetUserData('hide_online') == 1) ? 'checked' : '');
		$page->AddParam('follow-nobody', (Habbo::GetUserData('hide_inroom') == 0) ? 'checked' : '');
		$page->AddParam('follow-friends', (Habbo::GetUserData('hide_inroom') == 1) ? 'checked' : '');
		$page->AddParam('not_activated', (Habbo::GetUserData('mail_verified') == 1) ? false : true);
		$page->AddParam('sel_enabled', (Habbo::GetUserData('block_trade') == 0) ? 'checked' : '');
		$page->AddParam('sel_disable', (Habbo::GetUserData('block_trade') == 1) ? 'checked' : '');
		$page->AddGeneric('comp-settings-preferences');
		break;
		
	case '2':
		$page->AddParam('email', Habbo::GetUserData('mail'));
		$page->AddGeneric('comp-settings-mail');
		break;
		
	case '3':
		$page->AddGeneric('comp-settings-password');
		break;
}

$tpl->AddTemplate($page);
$tpl->AddLine('</div></div>');
	
$tpl->AddGeneric('community-column3');
$tpl->AddGeneric('footer');

echo $tpl;
?>