<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class SideNews extends Cache
{
	private static $promo;
	private static $widelist;
	private static $count = 0;
	private static $promo_count;
	
	public static function SEO($string)
	{
		$string = preg_replace("`\[.*\]`U", "", $string);
		$string = preg_replace('`&(amp;)?#?[a-z0-9]+;`i', '-', $string);
		$string = htmlentities($string, ENT_COMPAT, 'utf-8');
		$string = preg_replace("`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);`i", "\\1", $string);
		$string = preg_replace(array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
		
		return strtolower(trim($string, '-'));
	}
	
	public function __Construct($news_promo = 4, $news_wide = 2)
	{
		parent::$flag = '';
		parent::$length = 1000;
		parent::$class = 'SideNews';
		if(parent::Exists())
		{
			return;
		}
		
		$even = 'odd';
		self::$promo_count = $news_promo;
		$result = Core::$DB->query('SELECT * FROM `site_news` ORDER BY `id` DESC LIMIT '.($news_promo + $news_wide));
		$i = 0;
		while($row = $result->fetch_assoc())
		{
			if($i != self::$promo_count)
			{
				$tpl = new Template();
				$tpl->AddParam('topstory', $row['topstory']);
				$tpl->AddParam('may_show', ($i != 0) ? 'display: none;' : '');
				$tpl->AddParam('id', $row['id']);
				$tpl->AddParam('seo', self::SEO($row['caption']));
				$tpl->AddParam('caption', $row['caption']);
				$tpl->AddParam('snippet', $row['snippet']);
				$tpl->AddGeneric('comp-sidenews-item');
				
				$i++;
				self::$count++;
				
				self::$promo .= $tpl;
				continue;
			}
		}
		
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
		
		$tpl->AddParam('news_promo', self::$promo);
		$tpl->AddGeneric('comp-sidenews');
		
		return (string)$tpl;
	}
}
?>