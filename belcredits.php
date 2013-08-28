<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('PAGE', 3);
define('PARRENT', 12);
define('MUST_LOGIN', true);
include('bootstrap.php');

$tpl = new Template('Bel-Credits kopen');
$tpl->AddGeneric('head');
$tpl->AddDefault();
$tpl->AddLine(new IncludeFile('{webbuild}/static/js/cbs2credits.js'));
$tpl->AddLine(new IncludeFile('{webbuild}/static/js/newcredits.js'));
$tpl->AddLine(new IncludeFile('{webbuild}/static/styles/cbs2credits.css'));
$tpl->AddLine(new IncludeFile('{webbuild}/static/styles/newcredits.css'));
$tpl->AddGeneric('head-end');

$tpl->AddParam('body_id', 'cbs2credits');
new CommunityMenu();
$tpl->AddTemplate(CommunityMenu::GetTemplate());

$tpl->AddLine('<div id="column1" class="column">');
$tpl->AddParam('belcredits', Habbo::GetUserData('vip_points'));
$tpl->AddGeneric('page-belcredits');
$tpl->AddLine('</div>');

$tpl->AddLine('<div id="column2" class="column"></div>');

$tpl->AddGeneric('community-column3');
$tpl->AddGeneric('footer');	

echo $tpl;
?>