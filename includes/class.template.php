<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

class IncludeFile
{
	private $line = array();
	
	public function __Construct($file)
	{
		switch(pathinfo($file, PATHINFO_EXTENSION))
		{
			case 'js':
				$this->line[] = '<script type="text/javascript" src="'.$file.'"></script>';
				break;
				
			case 'css':
				$this->line[] = '<link type="text/css" href="'.$file.'" rel="stylesheet" media="screen">';
				break;
		}
	}
	
	public function __Tostring()
	{
		return implode($this->line);
	}
}

class Template
{
	private $params, $content = array();
	
	public function __Construct($title = '')
	{	
		$this->AddParam('url', URL);
		$this->AddParam('webbuild', WEBBUILD);
		$this->AddParam('site_name', SITE_NAME);
		$this->AddParam('full_name', SITE_NAME.' Hotel');
		$this->AddParam('site_title', SITE_NAME.' Hotel: '.ucfirst($title));
		$this->AddParam('users_online', Core::$DB->query('SELECT count(*) FROM users WHERE online = "1"')->result());
		
		$this->AddParam('logged_in', Habbo::LoggedIn() ? 'true' : 'false');
		$this->AddParam('user_name', USER_NAME);
		$this->AddParam('user_id', USER_ID);
		$this->AddParam('user_token', isset($_SESSION['user']['token']) ? $_SESSION['user']['token'] : '');
		$this->AddParam('twitter_username', Core::$Config['site']['twitter']);
		
		$this->AddParam('cookie_link', SITE_NAME.' maakt gebruik van cookies. Cookies? Kan je dat eten? Klik <a target="blank" href="https://help.habbo.nl/entries/21940398-habbo-nl-maakt-gebruik-van-cookies">hier</a> voor meer info.');
		$this->AddParam('copyright', 'Copyright &copy; '.date('Y').' Project Resource - Alpha Emulator &amp; '.SITE_NAME.' Hotel. Alle rechten voorbehouden.');
		$this->AddParam('footer_links', '<a href="'.URL.'">Homepagina</a> | <a href="'.URL.'/papers/termsAndConditions">Algemene voorwaarden</a> | <a href="'.URL.'/papers/privacy">Privacyverklaring</a>');
	}
	
	public function AddDefault($type = 'default')
	{
		switch($type)
		{
			case 'frontpage':
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/frontpage.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs2.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/landing.js'));
				break;
				
			case 'register':
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/common.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs2.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/visual.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/common.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/quickregister.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/quickregister.js'));
				break;
				 
			case 'settings':	
			case 'identity':
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/embed.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/embed.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/common.css'));
				if($type == 'identity')
				{
					$this->AddLine(new IncludeFile('{webbuild}/static/styles/avatarselection.css'));
					break;
				}
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/embeddedregistration.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/identitysettings.css'));
				break;
					
			case 'confirm':
			case 'forgot':
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/common.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/process.css'));
				if($type == 'forgot')
				{
					$this->AddLine(new IncludeFile('{webbuild}/static/styles/frontpage.css'));
				}
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs2.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/visual.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/common.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/fullcontent.js'));
				break;
					
			case 'issue':
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/embed.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/embed.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/identity.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/identity.css'));
				break;
				
			case 'homes':
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs2.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/visual.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/common.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/fullcontent.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/homeview.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/common.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/home.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/group.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/lightwindow.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/myhabbo/myhabbo.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/myhabbo/skins.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/myhabbo/dialogs.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/myhabbo/buttons.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/myhabbo/control.textarea.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/myhabbo/boxes.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/myhabbo/assets.css'));
				break;
					
			case 'default':	
			default:
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/common.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/personal.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/minimail.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/styles/lightweightmepage.css'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs2.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/visual.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/libs.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/common.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/fullcontent.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/lightweightmepage.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/minimail.js'));
				$this->AddLine(new IncludeFile('{webbuild}/static/js/habboclub.js'));
				break;
		}
	}
	
	public function AddGeneric($file)
	{
		if(!file_exists(ROOT.'templates'.DS.$file.'.html'))
		{
			die(ROOT.'templates'.DS.$file.'.html does not exists!');
		}
		
		$this->AddLine(file_get_contents(ROOT.'templates'.DS.$file.'.html'));
	}
	
	public function AddParam($key, $value)
	{
		$this->params['{'.$key.'}'] = $value;
	}
	
	public function AddLine($template)
	{
		$this->content[] = $template;
	}
	
	public function AddTemplate($class)
	{
		$this->AddLine($class);
	}
	
	public function ReplaceParams($content)
	{
		foreach($this->params as $key => $value)
		{
			$this->ReplaceBoolean($key, $value, $content);
		}
		
		return strtr($content, $this->params);
	}
	
	private function ReplaceBoolean($key, $value, &$content)
	{
		$begin = 0;
		while($begin = @strpos($content, '{if '.$key.'}', $begin + 1))
		{
			$endline = substr($content, $begin, strpos($content, '{endif}', $begin) + 7 - $begin);
			
			if($value == 'true' || filter_var($value, FILTER_VALIDATE_BOOLEAN))
			{
				$content = str_replace($endline, substr($endline, strlen('{if '.$key.'}'), strlen($endline) - strlen('{if '.$key.'}') - 7), $content);	
			}
			else
			{
				$content = str_replace($endline, '', $content);
			}
		}
	}
	
	public function __ToString()
	{
		foreach(Core::$Language as $key => $value)
		{
			$this->AddParam('language_'.$key, $value);
		}
		
		return $this->ReplaceParams(implode(PHP_EOL, $this->content));
	}
}
?>