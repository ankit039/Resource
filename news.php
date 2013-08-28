<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('PAGE', 2);
define('MUST_LOGIN', true);
include('bootstrap.php');
	
$tpl = new Template('Nieuws');
$tpl->AddGeneric('head');
$tpl->AddDefault();
$tpl->AddGeneric('head-end');

$parent = 9;
$cat = '';
if(isset($_GET['cat']))
{
	$cat = $_GET['cat'];
	$result = Core::$DB->prepare('SELECT `id` FROM `site_menu` WHERE `url` = "{url}/community/'.$cat.'" LIMIT 1')->execute();
	if(!($parent = @$result->result()))
	{
		$tpl = new Template();
		$tpl->AddGeneric('head');
		$tpl->AddDefault('issue');
		$tpl->AddGeneric('head-end');
		
		$tpl->AddGeneric('page-issue');
		
		echo $tpl;
		exit;
	}
}
define('PARRENT', 9);
	
$tpl->AddParam('body_id', 'news');
new CommunityMenu();
$tpl->AddTemplate(CommunityMenu::GetTemplate());
	
new News($cat);
$id = @current(News::$items);
$id = $id['id'];
if(isset($_GET['id']))
{
	$id = current(explode('-', $_GET['id']));
}

$tpl->AddLine('<div id="column1" class="column">');
//Content column 1

$tpl->AddTemplate(News::GetItems());
	
$tpl->AddLine('</div>');
$tpl->AddLine('<div id="column2" class="column">');
//Content column 2
	
$tpl->AddTemplate(News::GetArticle($id));

$tpl->AddLine('</div>');
	
$tpl->AddGeneric('community-column3');

$tpl->AddGeneric('footer');
	
echo $tpl;
?>