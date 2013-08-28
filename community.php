<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('PAGE', 2);
define('PARRENT', 8);
define('MUST_LOGIN', true);
include('bootstrap.php');

$tpl = new Template('Community');
$tpl->AddGeneric('head');
$tpl->AddDefault();
$tpl->AddGeneric('head-end');

$tpl->AddParam('body_id', 'home');
new CommunityMenu();
$tpl->AddTemplate(CommunityMenu::GetTemplate());

new SideNews();
$tpl->AddTemplate(SideNews::GetTemplate());

$tpl->AddLine('<div id="column2" class="column">');
$tpl->AddParam('twitter_username', Core::$Config['site']['twitter']);
$tpl->AddGeneric('comp-twitter');
$tpl->AddLine('</div>');

$tpl->AddLine('<div id="column1" class="column">');
$tpl->AddGeneric('comp-community');
$tpl->AddLine('</div>');

$tpl->AddGeneric('footer');	
echo $tpl;
?>