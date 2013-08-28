<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class Core
{
	public static $DB;
	public static $Config;
	public static $Language;
	
	public function __Construct($Config)
	{
		self::$Config = $Config;
		self::$Language = include(ROOT.'includes'.DS.'languages'.DS.Core::$Config['site']['language'].'.php');
		
		self::$DB = new Database(
		self::$Config['engine']['hostname'],
		self::$Config['engine']['username'],
		self::$Config['engine']['password'],
		self::$Config['engine']['database']);
		
		self::CheckSession(true);
		self::FilterControl(array(&$_GET, &$_POST, &$_COOKIE));
	}
	
	public static function Location($page)
	{
		return die(header('location: '.$page));
	}
	
	public static function Hash($string)
	{
		$string = hash('sha256', $string);
		$string = hash('sha512', $string);
		
		return sha1($string.'89V3498CCVDFSSD4FJUDFHUD78d355CD3D8FGF6SDFG');
	}
	
	public static function Clean(&$string)
	{
		if(is_array($string))
		{
			array_walk($string, array('self', 'Clean'));
			return;
		}
		
		$string = htmlentities(addslashes(stripslashes(trim($string))));
	}
	
	public static function RealString($string)
	{
		$string = str_replace('\\\'', '\'', $string);
		$string = str_replace(array('</p>', '<br />'), '<br>', $string);
		return str_replace(array('\\n\\r', '\\n', '\\r', 'rn', '<p>'), '', $string);
	}
	
	public static function CheckSession($OnlySession = false)
	{
		if(Habbo::LoggedIn())
		{
			if($OnlySession)
			{
				Habbo::UpdateUserData($_SESSION['user']['id'], true);
				return true;
			}
			
			return Habbo::Login($_SESSION['user']['username'], $_SESSION['user']['password']);
		}
		
		return false;
	}
	
	public static function NiceDate($string = '')
	{
		if(Core::$Config['site']['language'] == 'dutch')
		{
			setlocale(LC_ALL, array('nl_NL', 'nld_NLD'));
		}
		
		if(empty($string))
		{
			$string = time();
		}
		
		if(!is_numeric($string))
		{
			$string = strtotime($string);
		}
		
		return strftime('%d-%b-%Y', $string).' '.date('H:i:s', $string);
	}
	
	private static function FilterControl($string)
	{
		return array_walk_recursive($string, array('self', 'Clean'));
	}
}
?>