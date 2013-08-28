<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('PAGE', 1);
define('PARRENT', 4);
define('MUST_LOGIN', true);
include('bootstrap.php');

$tpl = new Template('Home');
$tpl->AddGeneric('head');
$tpl->AddDefault('default');
$tpl->AddGeneric('head-end');

$tpl->AddParam('body_id', 'home');
new CommunityMenu();
$tpl->AddTemplate(CommunityMenu::GetTemplate());

new UserInfoMe();
$tpl->AddTemplate(UserInfoMe::GetTemplate());

new SideNews();
$tpl->AddTemplate(SideNews::GetTemplate());

$tpl->AddLine('<div id="column1" class="column">');
$tpl->AddGeneric('comp-twitter');
$tpl->AddLine('</div>');

$tpl->AddLine('<div id="column2" class="column">');
$tpl->AddLine('</div>');

$tpl->AddGeneric('community-column3');
$tpl->AddGeneric('footer');

echo $tpl;
?>