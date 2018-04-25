<?php
//return '';

require_once MODX_BASE_PATH . 'assets/modules/evoShop/evoshop.class.php';
$evoShop = evoShop::getInstance($modx);
$evoShop->init();

$settings = $evoShop->getConfig();	

$settings['update'] = $_SESSION[$evoShop->cart->sessionKey] ? false : true;

$settings_json = json_encode($settings, JSON_UNESCAPED_UNICODE);
$modx->regClientScript('<script type="text/javascript">var evoShopConfig = '.$settings_json.';</script><script src="/assets/modules/evoShop/js/evoShop.js?v=1.25"></script>');
$modx->regClientCSS('<link rel="stylesheet" type="text/css" href="/assets/modules/evoShop/js/helpers.css">');

$cart_arr = $evoShop->cart->render();
//var_dump($cart_arr);

$phs = [];

//Settings
	$phs = array_merge($phs, $evoShop->config);
	$phs = array_merge($phs, $cart_arr);

//Mini-cart
if(count($cart_arr['items'])> 0) {
	$phs['miniCart'] = $modx->parseText($evoShop->config['templates']['fillMiniCartTpl'], $cart_arr, '{+', '+}');
} else {
	$phs['miniCart'] = $evoShop->config['templates']['emptyMiniCartTpl'];
}

//FullCart
if(count($cart_arr['items'])> 0) {
	$cartRows='';	
	foreach($cart_arr['items'] as $hash=>$item) {
		$item['hash'] = $hash;
		$cartRows .= $modx->parseText($evoShop->config['templates']['fullCartRowTpl'], $item, '{+', '+}');
	}
	$phs['fullCart'] = $cartRows;
}





$modx->toPlaceholders($phs);
return;