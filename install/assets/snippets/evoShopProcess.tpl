//<?php

/**
 * evoShopProcess
 *  
 * Prepare-сниппет для FormLister при оформлении заказа.
 *  
 * @category 	   snippet
 * @version 	   0.0.1
 * @internal	   @properties 
 * @internal	   @modx_category evoShop
 * @internal   	   @installset base, sample 
 */

if(!$modx->evoShop) {
	require_once MODX_BASE_PATH . 'assets/modules/evoShop/evoshop.class.php';
	$modx->evoShop = evoShop::getInstance($modx);
}

$modx->evoShop->cart['email'] = $FormLister->getField('email');
$modx->evoShop->cart['name'] = $FormLister->getField('name');
$modx->evoShop->cart['phone'] = $FormLister->getField('phone');

mail($modx->getConfig('emailsender'), 'cart contents', '<pre>'.print_r($data,1).'</pre>');