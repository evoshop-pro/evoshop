//<?php
/**
 * evoShopAjax
 *
 * evoShopAjax
 *
 * @category    plugin
 * @version     0.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      Bilbo Baggins aka autogen
 * @internal    @properties
 * @internal    @events OnPageNotFound
 * @internal    @modx_category evoShop
 * @internal    @installset base
 * @lastupdate  05/25/2018
 * @reportissues https://github.com/modxcms/evolution
 */

if(!defined('MODX_BASE_PATH')) die('What are you doing? Get out of here!');

$e = &$modx->Event;

switch($e->name){			
	case 'OnPageNotFound':		
		switch($_GET['q']){     
			case 'evoshop-ajax':
				require_once MODX_BASE_PATH . 'assets/modules/evoShop/ajax.php';
				die();		
			break;	
		}
		break;
}

//?>