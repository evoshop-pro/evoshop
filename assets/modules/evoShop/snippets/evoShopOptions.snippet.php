<?php

	require_once MODX_BASE_PATH . 'assets/modules/evoShop/evoshop.class.php';
	$evoShop = evoShop::getInstance($modx);

	$DLTemplate = DLTemplate::getInstance($modx);
	
	$id = $id ? $id : $modx->documentIdentifier;
	$product = $evoShop->getDoc($id);

	$outerTpl = $outerTpl ? $outerTpl : '@CODE:<div class="options-group"><label>[+caption+]</label>[+wrapper+]</div>';
	$innerRadioTpl = $innerRadioTpl ? $innerRadioTpl : '@CODE:<label class="radio">[+input+] [+name+] [[if? is=`[+price+]:>:0` &then=`([+price_format+])`]]</label>';
	$innerCheckboxTpl = $innerCheckboxTpl ? $innerCheckboxTpl : '@CODE:<label class="radio">[+input+] [+name+] [[if? is=`[+price_format+]:notempty` &then=`([+price_format+])`]]</label>';
	

	$price = $evoShop->getPriceByDoc($product, []);
	$currency = $product->get($evoShop->config['generalTV']['currency']);

	$out = '';
	$needFields = explode(',',$tv);


	foreach ($needFields as $field) {
		$tv_value = $product->get($field);
		$tv_caption = $evoShop->translate('[%'.$field.'%]');

		$paramArr = $evoShop->optionsParseString($tv_value);
		$typeElement = $paramArr['type'];
		
		

		if(count($paramArr['params'])<1) continue;
		
		$optionsGroup = '';

			if($typeElement=='select') {
				$optionsGroup .= '<select name="'.$field.'">';
			}

			$radio = 0;

			foreach ($paramArr['params'] as $paramName=>$dataPrice) {
				$symbol = preg_replace("/[,.0-9]/", '', $dataPrice);
				$change = preg_replace("/[^,.0-9]/", '', $dataPrice);
				
				if($symbol != '*') {
					$changePrice = $change > 0 ? $evoShop->toCurrency($change, $currency) : '';
				} else {
					$changePrice = $change;
				}

				$dataPrice = $symbol.$changePrice;
				if($dataPrice) {
					$dataPriceFormat = $symbol == '*' ? $evoShop->price_format($price*$changePrice) : $symbol.$evoShop->price_format($changePrice);
				} else {
					$dataPriceFormat = 0;
				}
				
				
				switch($typeElement) {
					case 'radio':
					default:
						$checked = ($radio==0) ? 'checked="checked"': '';	

						$input = '<input type="radio" name="'.$field.'" value="'.$paramName.'" data-price="'.$dataPrice.'" '.$checked.'>';
						
						$optionsGroup .= $DLTemplate->parseChunk($innerRadioTpl, ['input'=>$input, 'name'=>$evoShop->translate($paramName), 'price'=>$dataPrice, 'price_format'=>$dataPriceFormat], true); 
						$radio++;
					break;

					case 'checkbox':
						
						$input = '<input type="checkbox" name="'.$field.'" value="'.$paramName.'" data-price="'.$dataPrice.'">';
						
						$optionsGroup .= $DLTemplate->parseChunk($innerCheckboxTpl, ['input'=>$input, 'name'=>$evoShop->translate($paramName), 'price'=>$dataPrice, 'price_format'=>$dataPriceFormat], true); 
					break;

					case 'select':
						
					$optionsGroup .= '<option value="'.$paramName.'" data-price="'.$dataPrice.'"> '.$evoShop->translate($paramName).' </option>';
						
					break;
						
						
				}


			}

			if($typeElement=='select') {
				$optionsGroup .= '</select>';
			}

		
		$out .= $DLTemplate->parseChunk($outerTpl, ['caption'=>$tv_caption, 'wrapper'=>$optionsGroup], true); 
		
	}
//if($out) {
	echo '<div class="es-options"><form data-basePrice="'.$price.'">'.$out.'</form></div>';
//}
