<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class News
{
	public static $items;
	private static $category;
	private static $pagemessages = 30;
	
	public function __Construct($category)
	{
		$extend = '';
		if(!empty($category))
		{
			$extend = 'WHERE `category` = "'.$category.'"';
		}
		
		$result = Core::$DB->query('SELECT `id`,`caption`,`date` FROM `site_news` '.$extend.' ORDER BY `id` DESC');
		
		if($result->num_rows == 0)
		{
			self::$items = 0;
		}
		
		while($row = $result->fetch_assoc())
		{
			self::$items[] = $row;
		}
		
		self::$category = $category;
	}
	
	public static function GetItems()
	{
		$link = (!empty(self::$category)) ? URL.'/community/'.self::$category : URL.'/articles';
		$item = new Template();
		$tpl = new Template();
		$tpl->AddParam('title', (!empty(self::$category)) ? self::$category : 'Nieuws');
		
		$item->AddLine('<h2>Laatste nieuws</h2><ul>');
		if(self::$items == 0)
		{
			$item->AddLine('<li></li>');
		}
		else
		{
			foreach(self::$items as $id => $row)
			{			
				$item->AddLine('<li><a href="'.$link.'/'.$row['id'].'-'.SideNews::SEO($row['caption']).'" class="article-'.$row['id'].'">'.$row['caption'].'</a></li>');
			}
		}
		
		$item->AddLine('</ul>');
		
		$tpl->AddParam('news-items', (string)$item);
		$tpl->AddGeneric('comp-news-items');
		
		return (string)$tpl;
	}
	
	public static function GetArticle($id)
	{
		$result = Core::$DB->query('SELECT `site_news`.*,`users`.`username` AS `poster` FROM `site_news` INNER JOIN `users` ON `users`.`id` = `site_news`.`user_id` WHERE `site_news`.`id` = "'.$id.'" LIMIT 1');
		$tpl = new Template();
		
		if($result->num_rows < 1)
		{
			$tpl->AddParam('id', '');
			$tpl->AddParam('caption', 'Geen nieuwsartikelen gevonden');
			$tpl->AddParam('category', '');
			$tpl->AddParam('snippet', '');
			$tpl->AddParam('content', 'Er zijn in deze rubriek geen nieuwsartikelen gevonden. Klik de \'Back\' knop van je browser om terug te gaan naar je vorige bezochte pagina.');
			$tpl->AddParam('date', Core::NiceDate(time()));
			$tpl->AddParam('poster', '{site_name} staff');
		}
		else
		{
			$row = $result->fetch_assoc();
		
			$tpl->AddParam('id', $row['id']);
			$tpl->AddParam('caption', $row['caption']);
			$tpl->AddParam('category', $row['category']);
			$tpl->AddParam('snippet', $row['snippet']);
			$tpl->AddParam('content', Core::RealString($row['content']));
			$tpl->AddParam('date', Core::NiceDate($row['date']));
			$tpl->AddParam('poster', $row['poster']);
		}
		
		$tpl->AddGeneric('comp-news-content');
		
		return (string)$tpl;
	}
}
?>