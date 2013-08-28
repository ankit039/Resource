<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class UserInfoMe extends Cache
{
	private static $content;
	
	public function __Construct()
	{
		parent::$flag = USER_ID;
		parent::$length = 60;
		parent::$class = 'UserInfoMe';
		if(parent::Exists())
		{
			return;
		}
		
		$tpl = new Template();		
		$date = Core::NiceDate(Habbo::GetUserData('last_online'));
		
		$tpl->AddParam('look', Habbo::GetUserData('look'));
		$tpl->AddParam('motto', Habbo::GetUserData('motto'));
		$tpl->AddParam('last_login', $date);
		$tpl->AddGeneric('comp-userinfo');
		
		self::$content = $tpl;
		parent::MakeCache(self:: GetTemplate());
	}
	
	public static function GetTemplate()
	{
		$tpl = new Template();
		if(parent::Exists())
		{
			$tpl->AddLine(parent::GetCache());
			
			return (string)$tpl;
		}
		
		$tpl->AddTemplate(self::$content);
		return (string)$tpl;
	}
}
?>