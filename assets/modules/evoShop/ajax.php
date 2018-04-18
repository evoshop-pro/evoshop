<?php
require_once MODX_BASE_PATH . 'assets/modules/evoShop/evoshop.class.php';
$evoShop = evoShop::getInstance($modx);
$evoShop->init();

$cart = $evoShop->cart;

	if(!$_POST['action']) return 'no action';

	switch(strtolower($_POST['action'])) {
		case 'add':		
			if(!isset($_POST['data']['items']) || !is_array($_POST['data']['items'])) {
				$modx->logEvent(123, 3, '$_POST[data][items] не является массивом<br/><pre>'.print_r($_POST, true).'</pre>', 'evoShop - evoShop.class.php');
			} else {
				foreach($_POST['data']['items'] as $item) {
					$result = $cart->add($item['id'], $item['quantity'], $item['options'], $item['img']);
				}
			}
		break;
			
		case 'remove':
			$result = $cart->remove($_POST['data']['hash']);
		break;
		
		case 'clean':
			$result = $cart->clean();
		break;
			
		case 'change':
			$result = $cart->change($_POST['data']['item']['hash'], $_POST['data']['item']['quantity']);
		break;
			
		default:
			$result = true;
		break;
	}

	if(!$result) {
		echo json_encode(['status'=>400, 'post'=>$_POST]);
		exit();
	}	


	//Записать корзину в БД или обновить
	$cart->save();



	echo json_encode(['status'=>200, 'response'=>$cart->render()], JSON_NUMERIC_CHECK);
	exit();
