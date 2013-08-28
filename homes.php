<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('MUST_LOGIN', true);
require_once('bootstrap.php');

$body_id = 'viewmode';
	
if(!isset($_SESSION['homes']['editmode']))
{
	$_SESSION['homes']['editmode'] = false;
}
	
$home = (isset($_GET['id'])) ? $_GET['id'] : '';
$type = (isset($_GET['type'])) ? $_GET['type'] : 'home';

if(Habbo::Name2Id($home) == USER_ID && !HomeManager::HomeExists(USER_ID))
{
	HomeManager::CreateHome(USER_ID);
}
	
if($home == USER_NAME)
{
	define('PAGE', 1);
	define('PARRENT', 5);
}
else
{
	define('PAGE', 2);
	define('PARRENT', 0);
}

new Homes($home, $type);

$tpl = new Template($home);
$tpl->AddGeneric('head');
$tpl->AddDefault();
	
if(!Homes::$exists)
{
	Core::Location(URL.'/error');
}
	
if(@Homes::$data['home_type'] == 0 && @Homes::$data['owner_id'] != USER_ID)
{
	$tpl->AddGeneric('head-end');
	
	$tpl->AddParam('body_id', 'home');
	new CommunityMenu();
	$tpl->AddTemplate(CommunityMenu::GetTemplate());
	
	$tpl->AddGeneric('page-homes-hidden');
	$tpl->AddGeneric('footer');
	
	exit($tpl);
}
	
$tpl->AddLine(new IncludeFile('{webbuild}/static/styles/home.css'));
$tpl->AddLine(new IncludeFile('http://www.habbo.com/myhabbo/styles/assets/other.css'));
$tpl->AddLine(new IncludeFile('http://www.habbo.com/myhabbo/styles/assets/backgrounds.css'));
$tpl->AddLine(new IncludeFile('http://www.habbo.com/myhabbo/styles/assets/stickers.css'));
$tpl->AddLine(new IncludeFile('{webbuild}/static/js/homeview.js'));
$tpl->AddLine(new IncludeFile('{webbuild}/static/styles/lightwindow.css'));
$tpl->AddLine(new IncludeFile('{webbuild}/static/js/homeauth.js'));
$tpl->AddLine(new IncludeFile('{webbuild}/static/styles/group.css'));
	
$tpl->AddGeneric('head-homes-override');
if(Homes::IsEditMode())
{
	$body_id = 'editmode';
	$tpl->AddParam('home_id', Homes::$data['id']);
	$tpl->AddGeneric('comp-homes-iseditmode');
}
	
$tpl->AddGeneric('head-end');
	
$tpl->AddParam('edit_mode', Homes::IsEditMode());
$tpl->AddParam('no_edit_mode', !Homes::IsEditMode());
$tpl->AddParam('may_edit', (!Homes::IsEditMode() && Homes::MayEdit()) ? true : false);
$tpl->AddParam('home_id', Homes::$data['id']);

$tpl->AddParam('body_id', $body_id);
new CommunityMenu();
$tpl->AddTemplate(CommunityMenu::GetTemplate());

$home = new Template();
$home->AddParam('home_name', Homes::$data['name']);
$home->AddParam('background', Homes::$data['background']);
$home->AddGeneric('page-homes');

$items = '';
foreach(Homes::$items as $item)
{
	$items .= $item;
}

$home->AddParam('home_items', $items);	
$tpl->AddTemplate($home);
	
echo $tpl;
?>