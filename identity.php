<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

define('MUST_LOGIN', true);
include('bootstrap.php');

$tpl = new Template('E-mail Login beheren');
$tpl->AddGeneric('head');
$tpl->AddDefault('settings');

$tpl->AddParam('is_error', 'false');
switch($_GET['key'])
{
	case 'settings':
		$date = Core::NiceDate(Habbo::GetUserData('last_online'));
		
		$tpl->AddGeneric('head-end');
		$tpl->AddParam('pw_changed', (isset($_GET['passwordChanged'])) ? 'true' : 'false');
		$tpl->AddParam('email_changed', (isset($_GET['emailChanged'])) ? 'true' : 'false');
		$tpl->AddParam('email', Habbo::GetUserData('mail'));
		$tpl->AddParam('last_alive', $date);
		$tpl->AddParam('support_auth', 'false');
		$tpl->AddGeneric('page-identity-settings');
		break;
		
	case 'email':
		if(isset($_POST['fromClient']))
		{
			$currentPassword = $_POST['currentPassword'];
			$newEmailAddress = $_POST['newEmailAddress'];
			
			if(Core::Hash($currentPassword) != Habbo::GetUserData('password'))
			{
				$pass = true;
				$tpl->AddParam('is_error', 'true');
				$tpl->AddParam('error', 'Wachtwoorden niet hetzelfde.');
			}
			elseif(Habbo::EmailExists($newEmailAddress))
			{
				$email = true;
				$tpl->AddParam('is_error', 'true');
				$tpl->AddParam('error', 'Dit mailadres is al in gebruik.');
			}
			elseif(!Habbo::ValidMail($newEmailAddress))
			{
				$email = true;
				$tpl->AddParam('is_error', 'true');
				$tpl->AddParam('error', 'Vul alsjeblieft een geldig mailadres in.');
			}
			else
			{
				Core::$DB->prepare('UPDATE `users` SET `mail` = "'.$newEmailAddress.'" WHERE `mail` = "'.Habbo::GetUserData('mail').'"')->execute();
				Habbo::SetUserData('mail', $newEmailAddress, USER_ID);
				
				Core::Location(URL.'/identity/settings&emailChanged=true');
			}
		}
		
		$tpl->AddGeneric('head-end');
		$tpl->AddParam('email', Habbo::GetUserData('mail'));
		$tpl->AddParam('current_error', (isset($pass)) ? 'state-error' : '');
		$tpl->AddParam('new_error', (isset($email)) ? 'state-error' : '');
		$tpl->AddParam('curval', (isset($currentPassword)) ? $currentPassword : '');
		$tpl->AddParam('newval', (isset($newEmailAddress)) ? $newEmailAddress : '');
		$tpl->AddGeneric('page-identity-email');
		break;
		
	case 'password':
		if(isset($_POST['fromClient']))
		{
			include(ROOT.'captcha'.DS.'securimage.php');
			$captcha = new Securimage();
			
			$currentPassword = $_POST['currentPassword'];
			$newPassword = $_POST['newPassword'];
			$repeatPassword = $_POST['retypedNewPassword'];
			
			if(Core::Hash($currentPassword) != Habbo::GetUserData('password'))
			{
				$curpass = true;
				$tpl->AddParam('is_error', 'true');
				$tpl->AddParam('error', 'Your current password didn\'t match.');
			}
			elseif(empty($newPassword) || strlen($newPassword) < 6)
			{
				$newpass = true;
				$tpl->AddParam('is_error', 'true');
				$tpl->AddParam('error', 'Wachtwoord is niet geldig. Kies een ander wachtwoord.');
			}
			elseif($newPassword != $repeatPassword)
			{
				$reppass = true;
				$tpl->AddParam('is_error', 'true');
				$tpl->AddParam('error', 'Your new password does not match the rewritten password.');
			}
			elseif($captcha->check($_POST['recaptcha_response_field']) == false)
			{
				$caperr = true;
				$tpl->AddParam('is_error', 'true');
				$tpl->AddParam('error', 'De code die je in hebt getypt is ongeldig. Probeer het nog eens.');
			}
			else
			{
				Core::$DB->prepare('UPDATE `users` SET `password` = "'.Core::Hash($newPassword).'" WHERE `mail` = "'.Habbo::GetUserData('mail').'"')->execute();
				Habbo::SetUserData('password', Core::Hash($newPassword), USER_ID);
				
				Core::Location(URL.'/identity/settings&passwordChanged=true');
			}
		}
	
		$tpl->AddGeneric('head-end');
		$tpl->AddParam('curval', (isset($currentPassword)) ? $currentPassword : '');
		$tpl->AddParam('newval', (isset($newPassword)) ? $newPassword : '');
		$tpl->AddParam('repval', (isset($repeatPassword)) ? $repeatPassword : '');
		$tpl->AddParam('current_error', (isset($curpass)) ? 'state-error' : '');
		$tpl->AddParam('new_error', (isset($newpass)) ? 'state-error' : '');
		$tpl->AddParam('repeat_error', (isset($reppass)) ? 'state-error' : '');
		$tpl->AddParam('captcha_error', (isset($caperr)) ? 'state-error' : '');
		$tpl->AddGeneric('page-identity-password');
		break;
		
	case 'issue':
	default:
		$tpl = new tpl('issue');
		$tpl->AddGeneric('head');
		$tpl->AddDefault('issue');
		$tpl->AddGeneric('head-end');
		$tpl->AddGeneric('page-issue');
		break;
}

echo $tpl;
?>