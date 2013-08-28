<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class HomeManager
{
	public static function HomeExists($id, $type = 'home')
	{
		return Core::$DB->prepare('SELECT null FROM site_homes WHERE type = ? AND owner_id = ?')->bind_param($type, $id)->execute()->num_rows > 0;
	}
	
	public static function CreateHome($user_id, $type = 'home', $group_name = '', $group_badge = '')
	{
		$home_id = Core::$DB->insert_array('site_homes', array('owner_id' => $user_id, 'type' => $type, 'group_name' => $group_name, 'group_badge' => $group_badge, 'background' => 'b_bg_colour_04'))->insert_id;
		
		/**
		HomeItem::NewItem($user_id, $home_id, 113, 115, 1, 2, 'noteitskin', 'LETOP! de paginas zijn nog in beta versie sommige onderdelen werken daarom nog niet');
		HomeItem::NewItem($user_id, $home_id, 516, 103, 3, 2, 'speechbubbleskin', 'Welkom op jouw persoonlijke pagina.');
		HomeItem::NewItem($user_id, $home_id, 302, 78, 4, 0, 'defaultskin', 'paper_clip_1');
		**/
		
		$extradata = addslashes(serialize(array('widget' => 'profiel', 'id' => $user_id)));
		HomeItem::NewItem($user_id, $home_id, 572, 101, 2, 1, 'defaultskin', $extradata);
		
		return true;
	}
}

class Homes
{
	public static $exists;
	public static $data;
	public static $items;

	public function __Construct($home, $type, $allow_cache = false)
	{
		if($allow_cache)
		{
			self::$exists =& $_SESSION['homes']['exists'];
			self::$data =& $_SESSION['homes']['data'];
			self::$items = unserialize($_SESSION['homes']['items']);
			return;
		}
		
		if($type == 'home')
		{
			$result = Core::$DB->prepare('SELECT `site_homes`.*,`users`.`username` AS `name` FROM `site_homes` INNER JOIN `users` ON `users`.`id` = `site_homes`.`owner_id` WHERE `users`.`username` = "'.$home.'" LIMIT 1')->execute();
		}
		elseif($type == 'group')
		{
			$result = Core::$DB->prepare('SELECT `site_homes`.*,`iste_homes`.`group_name` AS `name` FROM `site_homes`  WHERE `site_homes`.`id` = "'.$home.'" LIMIT 1')->execute();
		}
		else
		{
			return;
		}
		
		if($result->num_rows < 1)
		{
			self::$exists = false;
			return;
		}
		
		self::$exists = true;
		self::$data = $result->fetch_assoc();
		
		self::LoadItems();
		self::UpdateAll();
	}
	
	public static function LoadItems()
	{
		$result = Core::$DB->prepare('SELECT * FROM `site_homes_items` WHERE `home_id` = "'.self::$data['id'].'"')->execute();
		
		self::$items = array();
		while($row = $result->fetch_assoc())
		{
			self::$items[$row['id']] = new HomeItem($row);
		}
	}
	
	public static function IsEditMode()
	{
		if(!self::MayEdit())
		{
			return false;
		}
		
		return @$_SESSION['homes']['editmode'];
	}
	
	public static function MayEdit()
	{
		if(@self::$data['owner_id'] == USER_ID)
		{
			return true;
		}
		
		return false;
	}
	
	public static function GetItemById($item_id)
	{
		foreach(self::$items as $item)
		{
			if($item->data['id'] == $item_id)
			{
				$item =& self::$items[$item->data['id']];
				return $item;
			}
		}
		
		return false;
	}
	
	public static function AddItem($item)
	{
		self::$items[$item->data['id']] = $item;
		self::UpdateItems();
	}
	
	public static function RemoveItem($item_id)
	{
		unset(self::$items[$item_id]);
		self::UpdateItems();
	}
	
	public static function UpdateAll()
	{
		$_SESSION['homes']['exists'] = self::$exists;
		$_SESSION['homes']['data'] = self::$data;
		self::UpdateItems();
	}
	
	public static function UpdateItems()
	{
		$_SESSION['homes']['items'] = serialize(self::$items);
	}
}

class HomeItem
{
	public $data;
	public $html;
	
	public function __Construct($data)
	{
		$this->data = $data;
		
		$this->Generate();
	}
	
	public function Generate()
	{
		if(!is_array($this->data))
		{
			return;
		}
		
		$tpl = new Template();
		$tpl->AddParam('skin', $this->data['skin']);
		$tpl->AddParam('x', $this->data['x']);
		$tpl->AddParam('y', $this->data['y']);
		$tpl->AddParam('z', $this->data['z']);
		$tpl->AddParam('id', $this->data['id']);
		
		switch($this->data['type'])
		{
			case 0: //sticker
				$tpl->AddParam('data', $this->data['extradata']);
				$tpl->AddGeneric('homes-sticker');
				break;
				
			case 1: //widget
				$data = unserialize($this->data['extradata']);
				switch($data['widget'])
				{
					case 'profiel':
						$row = Core::$DB->prepare('SELECT `id`,`username`,`look`,`motto`,`account_created`,`online` FROM `users` WHERE `id` = "'.$data['id'].'"')->execute()->fetch_assoc();
						
						$tpl->AddParam('username', $row['username']);
						$tpl->AddParam('look', $row['look']);
						$tpl->AddParam('motto', $row['motto']);
						$tpl->AddParam('date', date('d-m-Y', strtotime($row['account_created'])));
						$tpl->AddParam('online', ($row['online'] == 1) ? 'online' : 'offline');
						$tpl->AddParam('show_report', 'none');
						$tpl->AddParam('userid', $row['id']);
						$tpl->AddParam('is_self', ($row['id'] == USER_ID) ? true : false);
						$tpl->AddParam('has_friend', false);
						
						$tpl->AddGeneric('homes-widget-profiel');
						break;
						
					case 'guestbook':
						break;
				}
				break;
			
			case 2:
				$tpl->AddParam('show_report', 'none');
				$tpl->AddParam('data', $this->data['extradata']);
				$tpl->AddGeneric('homes-stickie');
				break;
				
			default:
				$tpl->AddParam('show_report', 'none');
				$tpl->AddParam('data', 'Oeps, Not a valid type');
				$tpl->AddGeneric('homes-stickie');
				break;
		}
		
		$this->html = $tpl;
	}
	
	public function SaveItem($new_data = array())
	{
		$sql = 'UPDATE `site_homes_items` SET';
		foreach($new_data as $key => $value)
		{
			if($key == 'id')
			{
				continue;
			}
			
			if($key == 'extradata' && $this->data['type'] == 1)
			{
				$value = serialize($value);
			}
			
			$this->data[$key] = $value;
			$sql .= ' `'.$key.'` = "'.$value.'", ';
		}
		
		$sql = substr($sql, 0, -2).' WHERE `id` = "'.$this->data['id'].'" LIMIT 1';
		Core::$DB->prepare($sql)->execute();
		
		$this->Generate();
	}
	
	function __Sleep()
	{
		return array('data');
	}
	
	function __Wakeup()
	{
		$this->Generate();
	}
	
	function __Tostring()
	{
		return (string)$this->html;
	}
	
	public static function Id2Type($id, $special = false)
	{
		switch($id)
		{
			case 0:
				return ($special) ? 'sticker' : 'stickers';
				break;
				
			case 1:
				return ($special) ? 'widget' : 'widgets';
				break;
				
			case 2:
				return ($special) ? 'stickie' : 'stickies';
				break;
				
			case 3:
				return ($special) ? 'background' : 'backgrounds';
				break;
				
			default:
				return '';
				break;
		}
	}
	
	public static function Id2Short($id)
	{
		switch($id)
		{
			case 0:
				return 's';
				break;
				
			case 1:
				return 'w';
				break;
				
			case 2:
				return 'n';
				break;
				
			case 3:
				return 'b';
				break;
			
			default:
				return '';
				break;
		}
	}
	
	public static function Type2Id($type, $special = false)
	{
		switch($type)
		{
			case (($special) ? 'sticker' : 'stickers'):
				return 0;
				break;
			
			case (($special) ? 'widget' : 'widgets'):
				return 1;
				break;
				
			case (($special) ? 'stickie' : 'stickies'):
				return 2;
				break;
				
			case (($special) ? 'background' : 'backgrounds'):
				return 3;
				break;
				
			case 'notes':
				return 2;
				break;
				
			default:
				return 0;
				break;
		}
	}
	
	public static function NewItem($owner_id, $home_id, $x = 0, $y = 0, $z = 0, $type = 0, $skin = '', $extradata = '')
	{
		return Core::$DB->prepare('INSERT INTO `site_homes_items` (owner_id, home_id, x, y, z, type, skin, extradata) VALUES ("'.$owner_id.'", "'.$home_id.'", "'.$x.'", "'.$y.'", "'.$z.'", "'.$type.'", "'.$skin.'", "'.$extradata.'")')->execute()->num_rows;
	}
	
	public static function AddToInventory($id, $remove = false)
	{
		$item = Homes::GetItemById($id);
		if($remove)
		{
			Homes::RemoveItem($id);
		}
		
		Core::$DB->prepare('DELETE FROM `site_homes_items` WHERE `id` = "'.$item->data['id'].'" LIMIT 1')->execute();
		if($item->data['type'] == 0)
		{
			$result = Core::$DB->prepare('SELECT `id` FROM `site_homes_inventory` WHERE `user_id` = "'.USER_ID.'" AND `type` = "'.$item->data['type'].'" AND `extradata` = "'.$item->data['extradata'].'" LIMIT 1')->execute();
			if($result->num_rows >= 1)
			{
				Core::$DB->prepare('UPDATE `site_homes_inventory` SET `amount` = `amount` +1 WHERE `id` = "'.$result->result().'" LIMIT 1')->execute();
			
				return $result->result();
			}
			else
			{
				return Core::$DB->prepare('INSERT INTO `site_homes_inventory` (user_id, type, extradata) VALUES ("'.USER_ID.'","0","'.$item->data['extradata'].'")')->execute()->insert_id;
			}
		}
	}
	
	public static function AddSticker($id, $z = 0, $x = 0, $y = 0, $skin = 'defaultskin')
	{
		$row = Core::$DB->prepare('SELECT * FROM `site_homes_inventory` WHERE `id` = "'.$id.'" LIMIT 1')->execute()->fetch_assoc();
		
		if($row['amount'] > 1)
		{
			Core::$DB->prepare('UPDATE `site_homes_inventory` SET `amount` = `amount` -1 WHERE `id` = "'.$id.'" LIMIT 1')->execute();
		}
		else
		{
			Core::$DB->prepare('DELETE FROM `site_homes_inventory` WHERE `id` = "'.$id.'" LIMIT 1')->execute();
		}
		
		$data['owner_id'] = USER_ID;
		$data['home_id'] = Homes::$data['id'];
		$data['x'] = $x;
		$data['y'] = $y;
		$data['z'] = $z;
		$data['type'] = $row['type'];
		$data['skin'] = $skin;
		$data['extradata'] = $row['extradata'];
		
		$id = Core::$DB->prepare('INSERT INTO `site_homes_items` (owner_id, home_id, x, y, z, type, skin, extradata) VALUES ("'.USER_ID.'", "'.$data['home_id'].'", "'.$x.'", "'.$y.'", "'.$z.'", "'.$row['type'].'", "'.$skin.'", "'.$row['extradata'].'")')->execute()->insert_id;
	
		$data['id'] = $id;
		$item = new HomeItem($data);
		
		return $item;
	}
}

class HomeEdits
{
	public $remove = array();
	public $skinChange = array();
	public $add = array();
	
	function __Construct()
	{	
		if(!isset($_SESSION['homes']['edit']['remove']))
		{
			$_SESSION['homes']['edit']['remove'] = array();
		}
		
		if(!isset($_SESSION['homes']['edit']['skinChange']))
		{
			$_SESSION['homes']['edit']['skinChange'] = array();
		}
		
		if(!isset($_SESSION['homes']['edit']['add']))
		{
			$_SESSION['homes']['edit']['add'] = array();
		}
			
		$this->remove =&  $_SESSION['homes']['edit']['remove'];
		$this->skinChange =&  $_SESSION['homes']['edit']['skinChange'];
		$this->add =& $_SESSION['homes']['edit']['add'];
	}
	
	public function AddData($type, $value)
	{
		$var =& $this->$type;
		$var[$value[0]] = $value[1];
	}
	
	public function ChangeSkin($item_id, $skin_id)
	{
		$skin = HomeEdits::GetSkinFromId($skin_id);
		$this->AddData('skinChange', array($item_id, $skin));
		
		$item = Homes::GetItemById($item_id);
		
		$item->data['skin'] = $skin;
		
		Homes::UpdateItems();
		
		return $item;
	}
	
	public function RemoveItem($item_id)
	{
		$item = Homes::GetItemById($item_id);
		
		$data[] = $item->data['type'];
		$data[] = $item->data['x'];
		$data[] = $item->data['y'];
		$data[] = $item->data['z'];
		$data[] = $item->data['skin'];
		$data[] = HomeItem::AddToInventory($item_id);
		
		$this->AddData('remove', array($item_id, $data));
	}
	
	public function AddItem($item_id, $type = 0, $data = null, $z = 0)
	{			
		switch($type)
		{
			case 0:
				$item = HomeItem::AddSticker($item_id, $z);
				break;
			
			case 1:
				$item = HomeItem::AddWidget($item_id, $z);
				break;
			
			case 2:
				//$item = HomeItem::AddSticker($item_id, $data, $z);
				break;
				
			default:
				return false;
				break;
		}

		$this->AddData('add', array($item->data['id'], true));

		Homes::AddItem($item);
		
		return $item;
	}
	
	public static function GetSkinFromId($skin_id)
	{
		switch($skin_id)
		{
			case 1:
				return 'defaultskin';
				break;
				
			case 6:
				return 'goldenskin';
				break;
			
			case 3:
				return 'metalskin';
				break;
			
			case 5:
				return 'notepadskin';
				break;
			
			case 2:
				return 'speechbubbleskin';
				break;
			
			case 4:
				return 'noteitskin';
				break;
				
			default: 
				return 'defaultskin';
				break;
		}
	}
}

class HomesCatalogus
{		
	public static $data;
	public static $name;
	public static $type;
	public static $cnts;
	public static $price;
	
	public static function IsSticker(&$type, &$data)
	{
		if($type == 'Stickie')
		{
			$data = 'commodity_stickienote_pre';
			$type = 'WebCommodity';
		}
	}
	
	public static function Generate($is_empty_webstore = true)
	{
		$tpl = new Template();
		
		$last = 0;
		$first_id = 0;
		$tpl->AddGeneric('homes-catalogus-webstore-categories');
		$result = Core::$DB->prepare('SELECT * FROM `site_homes_categories`')->execute();
		while($row = $result->fetch_assoc())
		{
			$class = 'subcategory';
			if($last == 0)
			{
				$first_id = $row['id'];
				$class = 'subcategory-selected';
			}
			
			$tpl->AddLine('<li id="subcategory-0-'.$row['id'].'-stickers" class="'.$class.'"><div>'.$row['caption'].'</div></li>');
			
			$last++;
		}
		$tpl->AddLine('</ul></li>');
		$tpl->AddGeneric('homes-catalogus-webstore-default'); //end rule 118
		
		$tpl->AddGeneric('homes-catalogus-webstore-items-1');
		if($is_empty_webstore)
		{
			for($i = 0; $i < 20; $i++)
			{
				$tpl->AddLine('<li class="webstore-item-empty"></li>');
			}
			
			$tpl->AddParam('preview-container', '');
		}
		else
		{
			$tpl->AddParam('preview-container', (string)self::GetCatalogusPreview(null, true));
			$tpl->AddTemplate(self::GenerateCatalogusItems($first_id));
		}
		
		$tpl->AddGeneric('homes-catalogus-webstore-items-2');
		$tpl->AddGeneric('homes-catalogus-inventory-menu');
		$tpl->AddGeneric('homes-catalogus-inventory-items-1');
		if(!$is_empty_webstore)
		{
			for($i = 0; $i < 20; $i++)
			{
				$tpl->AddLine('<li class="inventory-item-empty"></li>');
			}
		}
		else
		{
			$tpl->AddTemplate(self::GenerateInventoryItems(0));
		}
		
		$tpl->AddGeneric('homes-catalogus-inventory-items-2');
		return $tpl;
	}
	
	public static function GenerateInventoryItems($page_id)
	{
		$tpl = new Template();
		$tpl->AddLine('<ul id="inventory-item-list">');
		
		$result = Core::$DB->prepare('SELECT * FROM `site_homes_inventory` WHERE `type` = "'.$page_id.'" AND `user_id` = "'.USER_ID.'"')->execute();
		$rows = ceil($result->num_rows /4);
		$max = ((4 *$rows) <= 20) ? 20 : (4 *$rows);
		for($i = 0; $i < $max; $i++)
		{
			if($row = $result->fetch_assoc())
			{
				$type = ucfirst(HomeItem::Id2Type($row['type'], true));
				if($i == 0)
				{
					self::$name = self::$data = $row['extradata'];
					self::$type = $type;
					self::$cnts = $row['amount'];
				}
				
				$data = HomeItem::Id2Short($row['type']).'_'.$row['extradata'];
				self::IsSticker($type, $data);
				
				$tpl->AddLine('<li id="inventory-item-'.$row['id'].'" 
					title="">
					<div class="webstore-item-preview '.$data.' '.$type.'">
						<div class="webstore-item-mask">
							 '.(($row['amount'] > 1) ? '<div class="webstore-item-count"><div>x'.$row['amount'].'</div>' : '').'
						</div>
					</div>
				</li>');
			}
			else
			{
				$tpl->AddLine('<li class="webstore-item-empty"></li>');
			}
		}
		
		$tpl->AddLine('</ul>');
		
		return $tpl;
	}
	
	public static function GenerateCatalogusItems($page_id, $type = 0)
	{
		$tpl = new Template();
		$tpl->AddLine('<ul id="webstore-item-list">');
		
		$result = Core::$DB->prepare('SELECT * FROM `site_homes_catalogus` WHERE `type` = "'.$type.'" AND `parent_id` = "'.$page_id.'"')->execute();
		
		$rows = ceil($result->num_rows /4);
		$max = ((4 *$rows) <= 20) ? 20 : (4 *$rows);
		for($i = 0; $i < $max; $i++)
		{
			if(($row = $result->fetch_assoc()))
			{
				$type = ucfirst(HomeItem::Id2Type($row['type'], true));
				
				if($i == 0)
				{
					self::$data = $row['data'];
					self::$type = $type;
					self::$name = $row['caption'];
					self::$price = $row['price_cr'];
					self::$cnts = $row['amount'];
				}
				
				$data = HomeItem::Id2Short($row['type']).'_'.$row['data'];
				
				self::IsSticker($type, $data);
				
				$tpl->AddLine('<li id="webstore-item-'.$row['id'].'" title="'.$row['caption'].'">
					<div class="webstore-item-preview '.$data.' '.$type.'">
						<div class="webstore-item-mask">
							'.(($row['amount'] > 1) ? '<div class="webstore-item-count"><div>x'.$row['amount'].'</div>' : '').'
						</div>
					</div>
				</li>');
			}
			else
			{
				$tpl->AddLine('<li class="webstore-item-empty"></li>');
			}
		}
		$tpl->AddLine('</ul>');
		
		return $tpl;
	}
	
	public static function GetCatalogusPreview($id, $is_new = false)
	{
		$credits = Habbo::GetUserData('credits');
		
		$tpl = new Template();
		if($is_new)
		{
			$tpl->AddParam('price', self::$price);
			$tpl->AddParam('credits', $credits);
			$tpl->AddParam('not_enough', ($credits < self::$price) ? false : true);
			$tpl->AddGeneric('homes-catalogus-webstore-preview');
			
			return $tpl;
		}
		
		$row = Core::$DB->prepare('SELECT * FROM `site_homes_catalogus` WHERE `id` = "'.$id.'" LIMIT 1')->execute()->fetch_assoc();
		
		$data = HomeItem::Id2Short($row['type']).'_'.$row['data'];
		$type = ucfirst(HomeItem::Id2Type($row['type'], true));
		$amount = $row['amount'];
		
		$tpl->AddParam('price', $row['price_cr']);
		$tpl->AddParam('credits', $credits);
		$tpl->AddParam('not_enough', ($credits < $row['price_cr']) ? false : true);
		$tpl->AddGeneric('homes-catalogus-webstore-preview');
		
		self::IsSticker($type, $data);		
		if($row['type'] == 3)
		{
			header('X-JSON:[{"bgCssClass":"'.$data.'","type":"'.$type.'","itemCount":'.$amount.',"previewCssClass":"'.$data.'","titleKey":"'.$row['caption'].'"}]');
		}
		else
		{
			header('X-JSON:[{"type":"'.$type.'","itemCount":'.$amount.',"previewCssClass":"'.$data.'","titleKey":"'.$row['caption'].'"}]');
		}
		
		return $tpl;
	}
	
	public static function GetInventoryPreview($id)
	{
		$tpl = new Template();
		$row = Core::$DB->prepare('SELECT * FROM `site_homes_inventory` WHERE `id` = "'.$id.'" LIMIT 1')->execute()->fetch_assoc();
		
		$tpl->AddParam('is_sticker', false);
		$tpl->AddGeneric('homes-catalogus-inventory-preview');
		
		$data = HomeItem::Id2Short($row['type']).'_'.$row['extradata'];
		$type = ucfirst(HomeItem::Id2Type($row['type'], true));
		$amount = ($row['amount'] < 1) ? 0 : $row['amount'];
		
		self::IsSticker($type, $data);
		
		header('X-JSON:["'.$data.'","'.$data.'","","'.$type.'",null,'.$amount.']');
		
		return $tpl;
	}
	
	public static function Confirm($id)
	{
		$row = Core::$DB->prepare('SELECT * FROM `site_homes_catalogus` WHERE `id` = "'.$id.'" LIMIT 1')->execute()->fetch_assoc();
		
		$data = HomeItem::Id2Short($row['type']).'_'.$row['data'];
		$type = ucfirst(HomeItem::Id2Type($row['type'], true));
		$amount = $row['amount'];
		
		self::IsSticker($type, $data);
		
		$tpl = new Template();
		$tpl->AddParam('data', $data);
		$tpl->AddParam('type', $type);
		$tpl->AddParam('is_amount', ($amount > 1) ? true : false);
		$tpl->AddParam('amount', $amount);
		$tpl->AddGeneric('homes-catalogus-webstore-confirm');
		
		return $tpl;
	}
	
	public static function BuyItem($id)
	{
		$row = Core::$DB->prepare('SELECT * FROM `site_homes_catalogus` WHERE `id` = "'.$id.'" LIMIT 1')->execute()->fetch_assoc();
		
		Habbo::UpdateUserData(USER_ID);
		$new_credits = Habbo::GetUserData('credits') - $row['price_cr'];
		if($new_credits < 0)
		{
			return 0;
		}
		
		Habbo::SetUserData('credits', $new_credits);
		$result = Core::$DB->prepare('SELECT `id` FROM `site_homes_inventory` WHERE `user_id` = "'.USER_ID.'" AND `type` = "'.$row['type'].'" AND `extradata` = "'.$row['data'].'" LIMIT 1')->execute();
		if($result->num_rows > 0)
		{
			$amount = ($row['amount'] > 1) ? $row['amount'] : 1;
			
			if($row['type'] == 3)
			{
				return 0;
			}
			
			return Core::$DB->prepare('UPDATE `site_homes_inventory` SET `amount` = `amount` +'.$amount.' WHERE `id` = "'.$result->result().'" LIMIT 1')->execute()->num_rows;
		}
		
		return Core::$DB->prepare('INSERT INTO `site_homes_inventory` (user_id, type, extradata, amount) VALUES ("'.USER_ID.'","'.$row['type'].'","'.$row['data'].'","'.$row['amount'].'")')->execute()->num_rows;
	}
}
?>