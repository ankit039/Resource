<?php
defined('ROOT') or die;
/**
 *
 * Project Resource
 * Copyright (C) 2013 Nick Monsma
 *
 */

## WEBSITE DEFAULTS;
$Config['site']['name'] 	   = 'Subba';
$Config['site']['url'] 		   = 'http://localhost/Resource';
$Config['site']['webbuild']    = 'http://localhost/Resource/web-gallery';
$Config['site']['twitter']     = 'FoxzoneNL';
$Config['site']['maintenance'] = false;
$Config['site']['language']    = 'dutch'; ## english : dutch;

## DATABASE INFORMATION;
$Config['engine']['hostname'] = '127.0.0.1';
$Config['engine']['username'] = 'root';
$Config['engine']['password'] = 'admin';
$Config['engine']['database'] = 'subbahotel';

## CLIENT SETTINGS;
$Config['client']['external_variables'] = $Config['site']['url'].'/external.php?type=external_variables';
$Config['client']['external_texts']     = $Config['site']['url'].'/external.php?type=external_flash_texts';
$Config['client']['productdata']        = $Config['site']['url'].'/external.php?type=productdata';
$Config['client']['furnidata']          = $Config['site']['url'].'/external.php?type=furnidata';
$Config['client']['flash_base']         = $Config['site']['url'].':30002';
$Config['client']['ip']  				= 'habflyhotel.nl';
$Config['client']['port'] 				= 30000;
?>