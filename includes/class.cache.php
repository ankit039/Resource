<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class Cache
{
	public static $class;
	public static $length;
	public static $flag;
	
	public static function FileName()
	{
		return ROOT.'includes'.DS.'cache'.DS.'cache-'.sha1(self::$class.self::$flag).'.cache';
	}
	
	public static function Exists()
	{
		return file_exists(self::FileName()) && filemtime(self::FileName()) + self::$length > time() ? true : false;
	}
	
	public static function MakeCache($data)
	{
		return file_put_contents(self::FileName(), gzcompress($data));
	}
	
	public static function CleanCache()
	{
		foreach(glob(ROOT.'includes'.DS.'cache'.DS.'*.cache') as $file)
		{
			return filemtime($file) +5000 < time() ? unlink($file) : null;
		}
		
		## Delete old cache;
	}
	
	public static function DeleteCache()
	{
		return unlink(self::FileName());
	}
	
	public static function GetCache()
	{
		return gzuncompress(file_get_contents(self::FileName()));
	}
	
	public static function DestroyCache()
	{
		foreach(glob(ROOT.'includes'.DS.'cache'.DS.'*.cache') as $file)
		{
			return unlink($file);
		}
		
		## Delete old cache;
	}
}
?>