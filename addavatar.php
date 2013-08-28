<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('MUST_LOGIN', true);
include('bootstrap.php');

if(MultiUser::Togo() == 0)
{
	Core::Location(URL.'/identity/avatars');
}

function CheckName($username)
{
	$error = '';
	if(strlen($username) < 1)
	{
		$error .= '<li><p class="error-message">Deze naam is te kort</p></li>';
	}
	
	if(Habbo::NameTaken($username))
	{
		$error .= '<li><p class="error-message">Deze gebruikersnaam is al in gebruik</p></li>';
	}
	
	if(!Habbo::ValidName($username))
	{
		$error .= '<li><p class="error-message">Deze gebruikersnaam is niet geldig</p></li>';
	}
	
	return $error;
}

$tpl = new Template('Voeg een nieuwe '.SITE_NAME.' toe');

if(isset($_POST['checkNameOnly']))
{
	$tpl->AddParam('is_error', '');
	$tpl->AddParam('is_good', 'true');
	$error = CheckName($_POST['bean_avatarName']);
	if(strlen($error) > 1)
	{
		$tpl->AddParam('is_error', 'state-error');
		$tpl->AddParam('is_good', 'false');
		
		$_SESSION['register']['error'] = $error;
	}
	
	$tpl->AddParam('username', $_POST['bean_avatarName']);
	$tpl->AddGeneric('comp-addavatar-namebox');
	
	echo $tpl;
	exit;
}
	
if(isset($_GET['avatarMessage']))
{
	if(!isset($_SESSION['register']['error']))
	{
		exit;
	}
	
	$tpl->AddParam('error', $_SESSION['register']['error']);
	$tpl->AddGeneric('comp-addavatar-error');
    unset($_SESSION['register']['error']);
	
    echo $tpl;
    exit;
}
	
if(isset($_POST['checkFigureOnly']))
{
	$tpl->AddParam('look', $_POST['bean_figure']);
	$tpl->AddLine('<h3>Preview</h3><img src="'.URL.'/habbo-imaging/avatarimage?figure={look}&direction=4&head_direction=4" width="64" height="110"/>');
	
	$_SESSION['register']['avatar'] = $_POST['bean_figure'];
	$_SESSION['register']['gender'] = $_POST['bean_gender'];
	
	echo $tpl;
	exit;
}

if(isset($_POST['__app_key']))
{		
	$username = $_POST['bean_avatarName'];
	if($_POST['__app_key'] != $_SESSION['register']['api_number'])
	{
		$tpl->AddGeneric('head');
		$tpl->AddDefault('issue');
		$tpl->AddGeneric('head-end');
			
		$tpl->AddGeneric('page-issue');
		
		echo $tpl;
		exit;
	}
		
	$error = CheckName($username);
	if(strlen($error) > 1)
	{
		$_SESSION['register']['username'] = $username;
		$_SESSION['register']['error'] = $error;
		Core::Location(URL.'/identity/add_avatar');
	}
	
	$email = Habbo::GetUserData('mail');
	$id = Habbo::Register($username, Habbo::GetUserData('password'), $email, $_SESSION['register']['gender'], $_SESSION['register']['avatar']);

	new MultiUser();
	unset($_SESSION['register']);
	
	Core::Location(URL.'/identity/useOrCreateAvatar/'.$id);
}

$_SESSION['register']['api_number'] = $id = uniqid();
$_SESSION['register']['avatar'] = 'hr-545-36.hd-600-1.ch-640-69.lg-715-64.sh-907-64.he-1608.ca-1805-64,s-0.g-1.d-4.h-4.a-0';
$_SESSION['register']['gender'] = 'm';
	
$tpl->AddGeneric('head');
$tpl->AddDefault('identity');
$tpl->AddGeneric('head-end');
	
$namebox = new Template();
$namebox->AddParam('is_error', (isset($_SESSION['register']['error'])) ? 'state-error' : '');
$namebox->AddParam('username', (isset($_SESSION['register']['username'])) ? $_SESSION['register']['username'] : '');
$namebox->AddParam('is_good', 'false');
$namebox->AddGeneric('comp-addavatar-namebox');

$tpl->AddParam('namebox', (string)$namebox);	
$tpl->AddParam('errorbox', '');

if(isset($_SESSION['register']['error']))
{
	$errorbox = new Template();
	$errorbox->AddParam('error', $_SESSION['register']['error']);
	$errorbox->AddGeneric('comp-addavatar-error');
	
	$tpl->AddParam('errorbox', (string)$errorbox);
}
	
$tpl->AddParam('api_number', $id);
$tpl->AddGeneric('page-addavatar');

echo $tpl;
unset($_SESSION['register']['error']);
?>