<?php
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

session_start();
error_reporting(E_ALL | E_NOTICE);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(dirname(__FILE__)).DS);

foreach(glob(ROOT.'includes'.DS.'*.php') as $includes)
{
    include $includes;
}

new Core($Config);

foreach(glob(ROOT.'includes'.DS.'component'.DS.'*.php') as $component)
{
    include $component;
}

if(isset($_SERVER['HTTP_CF_CONNECTING_IP']))
{
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

define('USER_IP', $_SERVER['REMOTE_ADDR']);
define('URL', Core::$Config['site']['url']);

define('SITE_NAME', Core::$Config['site']['name']);
define('WEBBUILD', Core::$Config['site']['webbuild']);

if(Habbo::LoggedIn())
{
    //if(Habbo::IsBanned() && defined('BANNEDPAGE'))
    //{
    //    Core::Location(URL.'/banned');
    //}
    
    define('USER_NAME', Habbo::GetUserData('username'));
    define('USER_ID', Habbo::GetUserData('id'));
}
else
{
    define('USER_NAME', '');
    define('USER_ID', 0);
}

define('MAINTENANCE', Core::$Config['site']['maintenance']);

if(Habbo::LoggedIn() && Habbo::NeedCheckSecure())
{
    if(!Core::CheckSession())
    {
        Habbo::Logout();
    }
    
    Habbo::UpdateSecure();
}

if(MAINTENANCE && !defined('IN_MAINTENANCE') && !defined('IN_HK'))
{
    if(!Habbo::IsAdmin(USER_ID))
    {
        Core::Location(URL.'/maintenance');
    }
}

if(defined('MUST_LOGIN') && !Habbo::LoggedIn())
{
    Core::Location(URL);
}

if(defined('REDIRECT') && Habbo::LoggedIn())
{
    Core::Location(URL.'/me');
}
?>