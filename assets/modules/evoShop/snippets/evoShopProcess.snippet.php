<?php
if(!$modx->evoShop) {
	require_once MODX_BASE_PATH . 'assets/modules/evoShop/evoshop.class.php';
	$modx->evoShop = evoShop::getInstance($modx);
}

$modx->evoShop->cart['email'] = $FormLister->getField('email');
$modx->evoShop->cart['name'] = $FormLister->getField('name');
$modx->evoShop->cart['phone'] = $FormLister->getField('phone');

mail('d@iliukhin.com', 'test', '<pre>'.print_r($data,1).'</pre>');
