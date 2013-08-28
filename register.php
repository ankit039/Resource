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
$tpl->AddGeneric('head');
$tpl->AddDefault('register');
$tpl->AddGeneric('head-end');

$tpl->AddParam('if_error', 'false');
$tpl->AddParam('if_no_error', 'true');
switch($_GET['move'])
{		
	case 1:
		if(isset($_SESSION['register']['data']['page']))
		{
			Core::Location(URL.'/quickregister/email_password');
		}
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{			
			if(empty($_POST['bean_month']) || empty($_POST['bean_day']) || empty($_POST['bean_year']))
			{
				$_SESSION['register']['data']['error'] = 'Type alsjeblieft een geldige datum <br />';
				Core::Location(URL.'/quickregister/age_gate/e/05x');
			}
			
			if(isset($_SESSION['register']['data']['error']))
			{
				unset($_SESSION['register']['data']['error']);
			}
			
			$_SESSION['register']['data']['page'] = 2;
			Core::Location(URL.'/quickregister/email_password');
		}
		
		$tpl->AddGeneric('page-register-move-1');
		break;
		
	case 2:		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$name = $_POST['bean_name'];
			$email = $_POST['bean_email'];
			$emailA = $_POST['bean_retypedEmail'];
			$password = $_POST['bean_password'];
			$checked = @$_POST['bean_termsOfServiceSelection'];
			
			$_SESSION['register']['data']['name'] = $name;
			$_SESSION['register']['data']['email'] = $email;
			$_SESSION['register']['data']['emailA'] = $emailA;
			$_SESSION['register']['data']['checked'] = 'checked';
			$_SESSION['register']['data']['password'] = $password;
		
			$error = '';
			$_SESSION['register']['data']['error'] = '';
			if(empty($checked))
			{
				$error .= '01x';
				$_SESSION['register']['data']['error'] .= ' Je moet de Algemene Voorwaarden accepteren voor je verder kunt. <br />';
			}
			if(!Habbo::ValidMail($email))
			{
				$error .= '02x';
				$_SESSION['register']['data']['error'] .= 'Vul alsjeblieft een geldig mailadres in. <br />';
			}
			if(Habbo::NameTaken($name))
			{
				$error .= '03x';
				$_SESSION['register']['data']['error'] .= 'Deze gebruikersnaam is al in gebruik. <br />';
			}
			if(!Habbo::ValidName($name))
			{
				$error .= '04x';
				$_SESSION['register']['data']['error'] .= 'Vul alsjeblieft een geldige gebruikersnaam in. <br />';
			}
			if(Habbo::EmailExists($email))
			{
				$error .= '05x';
				$_SESSION['register']['data']['error'] .= 'Dit mailadres is al in gebruik. <br />';
			}
			if(empty($emailA))
			{
				$error .= '06x';
				$_SESSION['register']['data']['error'] .= 'Type opnieuw je mailadres <br />';
			}
			if(empty($password))
			{
				$error .= '07x';
				$_SESSION['register']['data']['error'] .= 'Wachtwoord <br />';
			}
			if($email != $emailA)
			{
				$error .= '08x';
				$_SESSION['register']['data']['error'] .= 'E-mailadressen zijn niet gelijk. <br />';
			}		
			if(strlen($name) <= 3)
			{
				$error .= '09x';
				$_SESSION['register']['data']['error'] .= 'Je gebruikersnaam moet langer zijn dan 3 tekens. <br />';
			}
			if(strlen($password) <= 5)
			{
				$error .= '10x';
				$_SESSION['register']['data']['error'] .= 'Je wachtwoord moet langer zijn dan 6 tekens. <br />';
			}
			
			if(strlen($error) > 1)
			{
				Core::Location(URL.'/quickregister/email_password/e/'.$error);
			}
			
			$_SESSION['register']['data']['page'] = 3;
			Core::Location(URL.'/quickregister/captcha');
		}
		
		$tpl->AddGeneric('page-register-move-2');
		break;
		
	case 3:		
		if(isset($_SESSION['register']['data']['page']))
		{
			if($_SESSION['register']['data']['page'] == 2)
			{
				Core::Location(URL.'/quickregister/email_password');
			}
		}
		else
		{
			Core::Location(URL.'/quickregister/start');
		}
		
		require ROOT.'captcha'.DS.'securimage.php';
		$captcha = new Securimage();
		
		if($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if($captcha->check($_POST['bean_captchaResponse']) == false)
			{
				$_SESSION['register']['data']['error'] = 'De code die je in hebt getypt is ongeldig. Probeer het nog eens. <br />';
				Core::Location(URL.'/quickregister/captcha/e/12x');
			}
			else
			{
				$email = $_SESSION['register']['data']['email'];
				$username = $_SESSION['register']['data']['name'];
				$password = $_SESSION['register']['data']['password'];
				
				Habbo::Register($username, Core::Hash($password), $email, 'M', 'hd-180-1.ch-210-66.lg-270-82.sh-290-91.hr-100');
				Habbo::Login($username, Core::Hash($password));
				
				Core::Location(URL.'/me');
			}
		}
		
		$tpl->AddGeneric('page-register-move-3');
		break;
		
	case 4:
		unset($_SESSION['register']['data']);
		Core::Location(URL.'/quickregister/start');
		break;
}

if(isset($_GET['error']) && $_GET['error'] == true)
{
	$tpl->AddParam('if_error', 'true');
	$tpl->AddParam('if_no_error', 'false');
		
	$tpl->AddParam('error_text', $_SESSION['register']['data']['error']);
}

echo $tpl;
?>