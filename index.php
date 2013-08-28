<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('REDIRECT', true);
include('bootstrap.php');

$tpl = new Template('Maak vrienden, doe mee en val op!');
$tpl->AddParam('forcedemail', 'false');
$tpl->AddParam('if_error', 'false');
$tpl->AddParam('changePwd', 'false');

function GenerateToken($length)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
	$string = '';
	for($i = 0; $i < $length; $i++)
	{
		$string .= $characters[rand(0, strlen($characters) - 1)];
    }
    
	return $string;
}

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$username = $_POST['credentials_username'];
	$password = $_POST['credentials_password'];
	
	if(Habbo::Login($username, Core::Hash($password), isset($_POST['_login_remember_me'])))
	{
		$_SESSION['user']['token'] = GenerateToken(10);
		Core::Location(URL.'/me');
	}
	else
	{
		$tpl->AddParam('if_error', 'true');
		$tpl->AddParam('error_text', 'Je gebruikersnaam en/of wachtwoord zijn niet correct ingevuld, probeer het opnieuw.');
	}
}

if(isset($_GET['changePwd']) && isset($_SESSION['mail']))
{
	$tpl->AddParam('changePwd', 'true');
	$tpl->AddParam('email', $_SESSION['mail']);
	unset($_SESSION['mail']);
}

if(isset($_COOKIE['secure_user'], $_COOKIE['secure_pass']))
{
	$result = Core::$DB->prepare('SELECT * FROM users WHERE username = ?')->bind_param($_COOKIE['secure_user'])->execute();
	
	if($result->num_rows > 0)
	{
		$row = $result->fetch_assoc();
		
		if(Core::Hash($row['password'].$row['username']) == $_COOKIE['secure_pass'])
		{
			Habbo::Login($row['username'], $row['password']);
		}
		else
		{
			habbo::Logout();
		}
	}
	
	Core::Location(URL.'/me');
}

$tpl->AddGeneric('page-frontpage');
echo $tpl;
?>