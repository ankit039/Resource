<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class CommunityMenu
{
	private static $mainmenu = array(), $submenu = array();
	
	public function __Construct()
	{
		$i = 0;
		$result = Core::$DB->prepare('SELECT * FROM site_menu WHERE (parent = ? OR parent = ?) AND min_rank <= ? AND enabled = ? ORDER by parent ASC')->bind_param(PAGE, '0', Habbo::GetUserData('rank'), '1')->execute();
		while($row = $result->fetch_assoc())
		{
			$i++;
			if($row['parent'] == 0)
			{
				if($row['id'] == PAGE)
				{
					self::$mainmenu[] = '<li id="'.$row['class'].'" class="'.$row['class'].' selected"><strong>'.$row['caption'].'</strong><span></span></li>';
				}
				else
				{
					self::$mainmenu[] = '<li id="'.$row['class'].'" class="'.$row['class'].'"><a href="'.$row['url'].'">'.$row['caption'].'</a><span></span></li>';
				}
				
				continue;
			}
			
			if($row['id'] == PARRENT)
			{
				self::$submenu[] = '<li class="'.$row['class'].' '.(($result->num_rows <= $i) ? 'last' : '').' selected">'.$row['caption'].'</li>';	
			}
			else
			{
				self::$submenu[] = '<li class="'.$row['class'].' '.(($result->num_rows <= $i) ? 'last' : '').'"><a href="'.$row['url'].'">'.$row['caption'].'</a></li>';
			}
		}
		
		self::$mainmenu = strtr(implode(PHP_EOL, self::$mainmenu), array('{user_name}' => '{user_name} (&nbsp;<i style="background-image: url({webbuild}/v2/images/rpx/icon_habbo_small.png);">&nbsp;</i>)'));
	}
	
	public static function GetTemplate()
	{
		$tpl = new Template();
		$tpl->AddParam('maintenance', MAINTENANCE);
		$tpl->AddParam('main-menu', self::$mainmenu);
		$tpl->AddParam('sub-menu', implode(PHP_EOL, self::$submenu));
		$tpl->AddGeneric('community-begin');
		return (string)$tpl;
	}
}
?>