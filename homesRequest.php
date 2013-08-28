<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

function GetUpdateData($string, &$update)
{
	$stickie = explode('/', $string);
	foreach($stickie as $value)
	{
		if(empty($value))
		{
			continue;
		}
		
		$data = explode(':', $value);
		$id = $data[0];
		$pos = explode(',', $data[1]);
		
		$update[$id]['x'] = $pos[0];
		$update[$id]['y'] = $pos[1];
		$update[$id]['z'] = $pos[2];
	}
}

function ValidateXY($x, $y)
{
	if($x < 0 || $x > 920)
	{
		return false;
	}
	
	if($y < 0 || $y > 1360)
	{
		return false;
	}
	
	return true;
}

define('MUST_LOGIN', true);
require_once('bootstrap.php');

$id = (isset($_GET['id'])) ? $_GET['id'] : '';
$type = (isset($_GET['type'])) ? $_GET['type'] : '';

new Homes(null, null, true);
$homeedit = new HomeEdits();

$tpl = new Template();
$tpl->AddParam('edit_mode', true);
switch($type)
{
	case 'pingsession':
		header('X-JSON: {"privilegeLevel":"1"}');
		break;
	
	case 'save':
		$update = array();
		foreach(Homes::$items as $item)
		{
			$id = $item->data['id'];
			
			if(isset($homeedit->remove[$id]))
			{
				HomeItem::AddToInventory($id, true);
			}
			
			if(isset($homeedit->skinChange[$id]))
			{
				$update[$id]['skin'] = $homeedit->skinChange[$id];
			}
		}
		
		if(isset($_POST['stickienotes']))
		{
			GetUpdateData($_POST['stickienotes'], $update);
		}
		
		if(isset($_POST['widgets']))
		{
			GetUpdateData($_POST['widgets'], $update);
		}
		
		if(isset($_POST['stickers']))
		{
			GetUpdateData($_POST['stickers'], $update);
		}
		
		if(isset($_POST['background']))
		{
			$data = explode(':', $_POST['background']);
			Core::$DB->prepare('UPDATE `site_homes` SET `background` = "'.$data[1].'" WHERE `id` = "'.Homes::$data['id'].'" LIMIT 1')->execute();
		}
		
		foreach(Homes::$items as $item)
		{
			if(!isset($update[$item->data['id']]))
			{
				continue;
			}
			
			$data = $update[$item->data['id']];
			
			if(!ValidateXY($data['x'], $data['y']))
			{
				continue;
			}
			
			$item->SaveItem($data);
		}
		
		$tpl->AddLine('<script language="javascript" type="text/javascript">');
		$tpl->AddLine('waitAndGo(\''.URL.'/home/'.Homes::$data['name'].'\');');
		$tpl->AddLIne('</script>');
		
		unset($_SESSION['homes']);
		break;
	
	case 'startSession':
		$_SESSION['homes']['editmode'] = true;
		return Core::Location(URL.'/home/'.USER_NAME);
		break;
		
	case 'cancel':
		foreach(Homes::$items as $item)
		{
			$id = $item->data['id'];
			if(isset($homeedit->add[$id]))
			{
				HomeItem::AddToInventory($id);
			}
			
			if(isset($homeedit->remove[$id]))
			{
				list($type, $x, $y, $z, $skin, $inv_id) = $homeedit->remove[$id];
				switch($type)
				{
					case 0:
						HomeItem::AddSticker($inv_id, $z, $x, $y, $skin);
						break;
					
					case 1:
						HomeItem::AddWidget($item_id, $z);
						break;
					
					case 2:
						//HomeItem::AddStickie($item_id, $data, $z);
						break;
				}
			}
		}
		
		unset($_SESSION['homes']);
		Core::Location($_SERVER['HTTP_REFERER']);
		break;
	
	case 'sticker':
		if($id == 'place_sticker')
		{
			$item_id = $_POST['selectedStickerId'];
		}
	
		$item_id = (!isset($item_id)) ? $_POST['stickerId'] : $item_id;
		
	case 'widget':
		$item_id = (!isset($item_id)) ? $_POST['widgetId'] : $item_id;
		
	case 'stickie':
		$item_id = (!isset($item_id)) ? $_POST['stickieId'] : $item_id;
		
		if($id == 'place_sticker')
		{
			if(!($item = $homeedit->AddItem($item_id, 0, null, $_POST['zindex'])))
			{
				break;
			}
			
			header('X-JSON:["'.$item->data['id'].'"]');
			
			$tpl->AddLine($item);
		}
		elseif($id == 'edit')
		{
			$skin_id = $_POST['skinId'];
			
			$item = $homeedit->ChangeSkin($item_id, $skin_id);
			
			$type = $item->data['type'];
			
			header('X-JSON: {"id":"'.$item_id.'","cssClass":"'.HomeItem::Id2Short($type).'_skin_'.$item->data['skin'].'","type":"'.HomeItem::Id2Type($type, true).'"}');
			
			echo 'SUCCESS';
		}
		elseif($id == 'delete' || $id == 'remove_sticker')
		{
			$homeedit->RemoveItem($item_id);
		}
		break;
		
	case 'noteeditor':
		if($id == 'editor')
		{
			$tpl->AddGeneric('homes-stickie-editor');
		}
		
		if($id == 'preview')
		{
			$tpl->AddParam('skin', HomeEdits::GetSkinFromId($_POST['skin']));
			$tpl->AddParam('message', $_POST['noteText']);
			$tpl->AddGeneric('homes-stickie-preview');
		}
		
		if($id == 'place')
		{
		}
		break;
	
	// Catalogus shit --------------------------------------------
	
	case 'store':
		if($id == 'inventory_items')
		{
			$type = HomeItem::Type2Id($_POST['type']);
			$tpl->AddTemplate(HomesCatalogus::GenerateInventoryItems($type));
			
			break;
		}
		elseif($id == 'items')
		{
			$type = $_POST['categoryId'];
			$page_id = $_POST['subCategoryId'];
			
			$tpl->AddTemplate(HomesCatalogus::GenerateCatalogusItems($page_id, $type));
			break;
		}
		elseif($id == 'preview')
		{
			$tpl->AddTemplate(HomesCatalogus::GetCatalogusPreview($_POST['productId']));	
			break;
		}
		elseif($id == 'inventory_preview')
		{
			$tpl->AddTemplate(HomesCatalogus::GetInventoryPreview($_POST['itemId']));	
			break;
		}
		elseif($id == 'background_warning')
		{
			$tpl->AddGeneric('homes-catalogus-background-warning');	
			break;
		}
		elseif($id == 'purchase_confirm')
		{
			$tpl->AddTemplate(HomesCatalogus::Confirm($_POST['productId']));	
			break;
		}
		elseif($id == 'purchase_stickers' || $id == 'purchase_backgrounds' || $id == 'purchase_stickie_notes')
		{
			if(HomesCatalogus::BuyItem($_POST['selectedId']) < 1)
			{
				$tpl->AddGeneric('homes-catalogus-webstore-error');	
				break;
			}
			
			echo 'OK';
			break;
		}
	
		$tpl->AddLine('<div style="position: relative;">');	
		if($id == 'inventory')
		{
			$tpl->AddTemplate(HomesCatalogus::Generate());
			
			$data = HomesCatalogus::$data;
			$name = HomesCatalogus::$name;
			$type = HomesCatalogus::$type;
			$cnts = HomesCatalogus::$cnts == null ? 0 : HomesCatalogus::$cnts;
			
			$data = HomeItem::Id2Short(0).'_'.$data;
			
			header('X-JSON:[["Mijn items","Homescatalogus"],["'.$data.'","'.$data.'","'.$name.'","'.$type.'",null,'.$cnts.']]');	
			break;
		}
		elseif($id == 'main')
		{
			$tpl->AddTemplate(HomesCatalogus::Generate(false));
			
			$data = HomesCatalogus::$data;
			$name = HomesCatalogus::$name;
			$type = HomesCatalogus::$type;
			$cnts = HomesCatalogus::$cnts == null ? 0 : HomesCatalogus::$cnts;
			
			$data = HomeItem::Id2Short(0).'_'.$data;
			
			header('X-JSON:[["Mijn items","Homescatalogus"],[{"type":"'.$type.'","itemCount":'.$cnts.',"previewCssClass":"'.$data.'","titleKey":"'.$name.'"}]]');	
			break;
		}
		
		$tpl->AddLine('</div>');
		break;
		
	default:
		$tpl->AddLine('<center><h3>404: Document not found!</h3></center>');
		break;
}

echo $tpl;
?>