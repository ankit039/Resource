<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('PAGE', 0);
define('PARRENT', 0);
define('MUST_LOGIN', true);
include('bootstrap.php');

$tpl = new Template('Pagina niet gevonden!');
$tpl->AddGeneric('head');
$tpl->AddDefault();
$tpl->AddGeneric('head-end');

$tpl->AddParam('body_id', 'home');
new CommunityMenu();
$tpl->AddTemplate(CommunityMenu::GetTemplate());

$tpl->AddLine('<div id="column1" class="column">');
$tpl->AddGeneric('page-404');
$tpl->AddLine('</div>');

$tpl->AddGeneric('community-column3');
$tpl->AddGeneric('footer');

echo $tpl;	
?>