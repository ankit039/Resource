<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('PAGE', 2);
define('PARRENT', 10);
define('MUST_LOGIN', true);
include('bootstrap.php');

$tpl = new Template('Medewerkers');
$tpl->AddGeneric('head');
$tpl->AddDefault();
$tpl->AddGeneric('head-end');

$tpl->AddParam('body_id', 'home');
new CommunityMenu();
$tpl->AddTemplate(CommunityMenu::GetTemplate());

$tpl->AddLine('<div id="column1" class="column">');
new StaffList();
$tpl->AddTemplate(StaffList::GetTemplate());
$tpl->AddLine('</div>');

$tpl->AddLine('<div id="column2" class="column">');
$tpl->AddGeneric('comp-staff-about');
$tpl->AddLine('</div>');

$tpl->AddGeneric('community-column3');
$tpl->AddGeneric('footer');

echo $tpl;
?>