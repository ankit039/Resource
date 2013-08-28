<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class StaffList
{
	private static $content;
	
	public function __Construct()
	{	
		$template = array();
		for($i = 7; $i > 2; $i--)
		{
			$color = '#FFFFFF';
			$content = array();
			
			$result = Core::$DB->prepare('SELECT username, look, motto, last_online, online FROM users WHERE rank = ? ORDER BY id DESC')->bind_param($i)->execute();
			while($row = $result->fetch_assoc())
			{
				$color = ($color == '#FFFFFF') ? '#ECECEC' : '#FFFFFF';
				$date = Core::NiceDate($row['last_online']);
				$badge = Core::$DB->query('SELECT badge FROM site_ranks WHERE rank = '.$i.'')->result();
				
				$item = new Template();
				$item->AddParam('badge', $badge);
				$item->AddParam('color', $color);
				$item->AddParam('look', $row['look']);
				$item->AddParam('motto', $row['motto']);
				$item->AddParam('username', $row['username']);
				$item->AddParam('last_online', $date);
				$item->AddParam('status', ($row['online'] == 1) ? 'online' : 'offline');
				$item->AddGeneric('comp-staff-content-item');
				
				$content[] = $item;
			}
			
			$tpl = new Template();
			$tpl->AddParam('staff_item', implode($content));
			$tpl->AddParam('staff_title', Core::$Language['staff_rankname_'.$i.'']);
			$tpl->AddGeneric('comp-staff-content');
		
			$template[] = $tpl;
		}
		
		self::$content = implode($template);
	}
	
	public static function GetTemplate()
	{
		$tpl = new Template();		
		$tpl->AddTemplate(self::$content);
		return (string)$tpl;
	}
}
?>