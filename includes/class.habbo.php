<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class Habbo
{
	public static $data;
	
	public static function LoggedIn()
	{
		return isset($_SESSION['user']['loggedin']);
	}
	
	public static function Login($habbo, $password, $rememberme = false)
	{
		$result = Core::$DB->prepare('SELECT `id` FROM `users` WHERE (`username` = ? OR mail = ?) AND `password` = ? LIMIT 1')->bind_param($habbo, $habbo, $password)->execute();
		if($result->num_rows == 1)
		{
			$id = $result->result();
			$username = self::Id2name($id);
			
			if($rememberme)
			{
				$expire = time() + 864000; ## + 10 days;
				$cookie_pass = Core::Hash($password.$username);
				
				setcookie('secure_user', $username, $expire, '/');
				setcookie('secure_pass', $cookie_pass, $expire, '/');
			}
			
			$_SESSION['user']['loggedin'] = true;
			$_SESSION['user']['username'] = $username;
			$_SESSION['user']['password'] = $password;
			$_SESSION['user']['id'] = $id;
			
			self::UpdateUserData($id);
			new MultiUser(true);
			
			return true;
		}
		
		return false;
	}
	
	public static function Logout()
	{
		setcookie('secure_user', null, 1, '/');
		setcookie('secure_pass', null, 1, '/');

		session_destroy();
	}
	
	public static function GetUserData($key, $id = 0, $allowCache = true)
	{
		if($id == 0 || self::$data['id'] == $id && $allowCache)
		{
			return self::$data[$key];
		}
		
		return Core::$DB->prepare('SELECT `'.$key.'` FROM `users` WHERE `id` = ? LIMIT 1')->bind_param($id)->execute()->result();
	}
	
	public static function UpdateUserData($id, $allowCache = false)
	{
		if($allowCache && isset($_SESSION['user']['data']))
		{
			self::$data = $_SESSION['user']['data'];
			return;
		}
			
		self::$data = $_SESSION['user']['data'] = Core::$DB->prepare('SELECT * FROM `users` WHERE `id` = ? LIMIT 1')->bind_param($id)->execute()->fetch_assoc(); 
		
		$_SESSION['user']['username'] = self::$data['username'];
	}
	
	public static function SetUserData($key, $value, $id = 0)
	{
		if($id == 0 || self::$data['id'] == $id)
		{
			self::$data[$key] = $_SESSION['user']['data'][$key] = $value;
		}
		
		return Core::$DB->prepare('UPDATE `users` SET `'.$key.'` = ? WHERE `id` = ? LIMIT 1')->bind_param($value, $id)->execute();
	}
	
	public static function UpdateSecure()
	{
		$_SESSION['user']['multi'][USER_ID]['last_update'] = time();
	}
	
	public static function NeedCheckSecure()
	{
		return !isset($_SESSION['user']['multi'][USER_ID]['last_update']) || $_SESSION['user']['multi'][USER_ID]['last_update'] + 450 <= time();
	}
	
	public static function ValidName($username)
	{
		return ctype_alnum($username) && strlen($username) <= 25;
	}
	
	public static function ValidMail($email)
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	
	public static function NameTaken($username)
	{
		return Core::$DB->prepare('SELECT null FROM `users` WHERE `username` = ? LIMIT 1')->bind_param($username)->execute()->num_rows > 0;
	}
	
	public static function EmailExists($email)
	{
		return Core::$DB->prepare('SELECT null FROM `users` WHERE `mail` = ? LIMIT 1')->bind_param($email)->execute()->num_rows > 0;
	}
	
	public static function Register($username, $password, $email, $gender, $look, $rank = 1)
	{
		return Core::$DB->prepare("INSERT INTO users (username, password, mail, auth_ticket, rank, look, gender, motto, credits, activity_points, last_online, account_created, ip_last, ip_reg) VALUES (?, ?, ?, '', ?, ?, ?, '', '500', '1000', '', '".date('d-M-Y')."', ?, ?)")->bind_param($username, $password, $email, $rank, $look, $gender, USER_IP, USER_IP)->execute()->insert_id;
	}
	
	public static function Id2Name($id)
	{
		return Core::$DB->prepare('SELECT `username` FROM `users` WHERE `id` = ? LIMIT 1')->bind_param($id)->execute()->result();
	}
	
	public static function Name2Id($username)
	{
		return Core::$DB->prepare('SELECT `id` FROM `users` WHERE `username` = ? LIMIT 1')->bind_param($username)->execute()->result();
	}
	
	public static function IsAdmin($id = 0)
	{
		return self::GetUserData('rank', $id) <= 5 ? false : true;
	}
	
	public static function IsBanned($id = 0)
	{
		if($id == 0)
		{
			$id = self::$data['id'];
		}
		
		return Core::$DB->prepare("SELECT null FROM `user_bans` WHERE (`user_id` = '".$id."' OR (`user_ip` = '".USER_IP."' AND `ip_ban` = '1')) AND `end_datetime` > '".time()."' LIMIT 1")->execute()->num_rows > 0;		 
	}
}

class MultiUser
{		
	public static $data;
	
	public static function IsValid($id = 0)
	{
		if(!Habbo::LoggedIn())
		{
			return false;
		}
		
		foreach($_SESSION['user']['multi'] as $value)
		{
			return (bool)$value['id'] == $id;
		}
		
		return false;
	}
	
	public static function Togo()
	{
		return 50 - count($_SESSION['user']['multi']);
	}
	
	public static function SwitchUser($id)
	{
		if(!self::IsValid($id))
		{
			return false;
		}
		
		Habbo::UpdateUserData($id, false);
		return true;
	}
	
	public function __Construct($allowCache = false)
	{
		if(!Habbo::LoggedIn())
		{
			return;
		}
		
		if($allowCache && isset($_SESSION['user']['multi']))
		{
			self::$data = $_SESSION['user']['multi'];
			return;
		}
		
		$result = Core::$DB->prepare('SELECT `id`,`username`,`look`,`last_online` FROM `users` WHERE `mail` = ?')->bind_param(Habbo::GetUserData('mail'))->execute();

		while($row = $result->fetch_assoc())
		{
			self::$data[$row['id']] = $row;
		}
		
		$_SESSION['user']['multi'] = self::$data;
	}
}
?>