<?php 
class Cart
{  
		
		public function __construct ($es, $config) {
			$this->modx = $es->modx;
			$this->es = $es;
			$this->sessionKey = 'evoshopCart';
			$this->cart_id = $_POST['cart_id'] ? $this->modx->db->escape($_POST['cart_id']) : md5($this->modx->getConfig('site_name').rand(0,9999).time());
			$this->cart = $this->toArray();
			$this->config = $config;
		}

		public function loadStorage() {
			if(!$_SESSION[$this->sessionKey] && $this->cart_id) {
				$cart_id = $this->cart_id;
				$q = $this->modx->db->select('cart', $this->modx->getFullTableName('evoshop_carts'), 'hash="'.$cart_id.'"');
				$cart = $this->modx->db->getRecordCount($q)==1 ? json_decode($this->modx->db->getValue($q), true) : ['items'=>[]];
				$this->cart_id = $cart_id;
			} else {
				$cart = $_SESSION[$this->sessionKey] ? $_SESSION[$this->sessionKey] : ['items'=>[]];
			}

			return $cart;
		}

		public function saveStorage() {
			$fields = ['cart'=>json_encode($this->cart, JSON_UNESCAPED_UNICODE)];
			$fields['ip'] = $_SERVER['REMOTE_ADDR'];
			$fields['userid'] = 0;


			if(count($this->cart['items'])<1 && $this->cart_id!==null) {
				$this->modx->db->delete($this->modx->getFullTableName('evoshop_carts'), 'hash="'.$this->cart_id.'"');
				$this->clean();
			}

			if(count($this->cart['items'])>0) {
				$q = $this->modx->db->select('cart', $this->modx->getFullTableName('evoshop_carts'), 'hash="'.$this->cart_id.'"');
				if($this->modx->db->getRecordCount($q)==1) {
					$this->modx->db->update($fields, $this->modx->getFullTableName('evoshop_carts'), 'hash="'.$this->cart_id.'"');
				} else {
					$fields['hash'] = $this->cart_id;
					$this->modx->db->insert($fields, $this->modx->getFullTableName('evoshop_carts'));
					$this->cart_id = $fields['hash'];
				}
			}

			$_SESSION[$this->sessionKey] = $this->cart;
		}


	    //Получение текущего состояния корзины
		public function toArray() {
			$cart = $this->loadStorage();

			//Событие перед получением корзины
			$resEvent = $this->es->callEvent('esOnLoadCart', array('cart' => $cart));
	        if(is_array($resEvent)) {
	           	$cart = $resEvent['cart'];
	        }

			return $cart;
		}
		

		//Сохранение корзины в БД или в сессию
		public function save() {
			//$this->recalculate();			
			return $this->saveStorage();
		}

		public function render() {

				$render = $this->cart?:[];		

				//Посчитать общую сумму и количество товаров в корзине
				$total_cnt = 0;
				$total_sum = 0;

				if($render['items'] && count($render['items'])>0) {
					/*$tvList = [$this->config['generalTV']['title']];

					$docIds = [];
					foreach($render['items'] as $hash=>$item) {
						$docIds[] = $item['id'];
					}

					$out = $this->es->modx->runSnippet('DocLister', ['saveDLObject' => '_DL', 'documents'=>implode(',', $docIds), 'tvPrefix'=>'', 'tvList'=>implode(',', $tvList) ]);
					$_DL = $this->es->modx->getPlaceholder('_DL');
					$docs = get_class_methods($_DL);
					$docs = $_DL->getDocs();*/

					foreach($render['items'] as $hash=>$item) {
						if(!$render['ids'][$item['id']]) {
							$render['ids'][] = $item['id'];
						}
						$product = $this->es->getDoc($item['id']);
						//$render['items'][$hash]['categoryName'] = $this->es->getDoc($render['items'][$hash]['category'])->get('pagetitle');	
						$render['items'][$hash]['name'] = $this->es->sanitize($product->get($this->config['generalTV']['title'])) ?: $this->es->sanitize($product->get('pagetitle'));	
						$render['items'][$hash]['url'] = $this->modx->makeUrl($item['id']);

						$render['items'][$hash]['price'] = $this->es->getPriceByDoc($product, $item['options']);

						$summ = $render['items'][$hash]['price']*$item['quantity'];
					
						$render['items'][$hash]['price_formatted'] = $this->es->price_format($render['items'][$hash]['price'], false);
						$render['items'][$hash]['price_formatted_sign']	= $this->es->price_format($render['items'][$hash]['price']);
						$render['items'][$hash]['summ_formatted']		=	$this->es->price_format($summ, false);
						$render['items'][$hash]['summ_formatted_sign']	=	$this->es->price_format($summ);				


						$total_cnt = ($item['units']=='pc') ? ($total_cnt + $item['quantity']) : $total_cnt+1;
						$total_sum = $total_sum + $summ;
					}
				}

				$render['total_cnt'] = $total_cnt;					
				$render['total_sum'] = round($total_sum + $upsale - $discount, 2);
				$render['total_sum_formatted'] = $this->es->price_format($render['total_sum'], false);
				$render['total_sum_formatted_sign'] = $this->es->price_format($render['total_sum']);

				$render['pluralProduct'] = $this->es->plural($total_cnt, 'product');
				$render['pluralCurrency'] = $this->es->plural($render['total_sum'], $this->es->currency);

				$render['currency'] = $this->es->currency;
				$render['lang'] = $this->es->lang;
				$render['cart_id'] = $this->cart_id;

				$resEvent = $this->es->callEvent('esOnRenderCart', array('cart' => $render));
		            if(is_array($resEvent)) {
		            	$render = $resEvent['cart'];
		            }

				$json = json_encode($render, JSON_UNESCAPED_UNICODE);
				
				/*preg_match_all("/\[\(__(.*?)\)\]/", $json, $findTranslateTags);
				foreach($findTranslateTags[1] as $translateTag) {
					$findTranslateTags[2][] = $this->es->translate($translateTag);
				}
				$json = str_replace($findTranslateTags[0], $findTranslateTags[2], $json);*/

				return json_decode($json,true);
		}
		
		public function recalculate() {
			$items = $this->cart['items'] ? $this->cart['items'] : [];
			$this->clean(false); //Удалили все товары из корзины

			foreach($items as $hash=>$item) {
				$item['options'] = $item['options'] && is_array($item['options']) ? $item['options'] : [];
				$this->add($item['id'], $item['quantity'], $item['options'], $item['imgFull']);
			}

			return true;
		}

		public function add($itemId, $quantity=1, $options=array(), $img='') {		
			if (empty($itemId) || !is_numeric($itemId)) {
	            return false;
	        }

	        if (is_string($options)) {
	            $options = json_decode($options, true);
	        }

	        if (!is_array($options)) {
	            $options = array();
	        }

	        if($product = $this->es->getDoc($itemId)) {
	        	//ID категории (родителя) товара
				$categoryId = $product->get('parent');
				//Если у документа отсутствует родитель (документ лежит в корне сайта) - значит это точно не товар, пропускаем его.
				if(!$categoryId) return false;

	            //Генерируем уникальный хэш для строки корзины
				$itemHash = md5($itemId . (json_encode($options)));

				//Получаем единицу измерения товара
				$units = $this->es->sanitize($product->get($this->config['generalTV']['unit']));
				$units = $units ? $units : 'pc';
					
				//Получаем шаг
				$step = $this->es->sanitize($product->get($this->config['generalTV']['step']), 'num');
				$step = $step>0 ? $step : 1;

				//Проверяем количество товара
				$quantity = floatval($this->es->sanitize($quantity, 'num'));
				$quantity = (preg_match('/^\+?\d+$/', $quantity/$step)) ? $quantity : $step;
				$quantity = $units!='pc' ? $quantity : ceil($quantity);
				$quantity = $quantity>0 ? $quantity : $step;

				//Тут полчаем цену товара и проверяем на наличие влияющих на цену параметров
				$price = $this->es->getPriceByDoc($product, $options);

					$new_product = array(
	                    'id'					=>	$itemId, 
						'quantity'				=>	$quantity, 
						'options'				=>	$options,
						'imgFull'				=>	$this->es->sanitize($img), 
						'units'					=>	$units,
						'step'					=>	$step,
						'category'				=>	$categoryId,
						'price'					=> 	$price ? $price : 0,
						'currency'				=>  $this->es->currency
	            	);


				//Событие перед добавлением товара в корзину
				$resEvent = $this->es->callEvent('esBeforeAddToCart', array('hash'	=> $itemHash, 'product' => $new_product, 'cart' => $this->cart['items']));
	            if(is_array($resEvent)) {
	            	$itemHash = $resEvent['hash'];
	            	$new_product = $resEvent['product'];
	            	$this->cart['items'] = $resEvent['cart'];
	            }

				//Если товар существует в корзине - то обновляем количество
				if (array_key_exists($itemHash, $this->cart['items'])) {
	                $addResult = $this->change($itemHash, $this->cart['items'][$itemHash]['quantity'] + $quantity);
	            //Иначе добавляем в корзину
	            } else {
	            	$this->cart['items'][$itemHash] = $new_product;
	            	$addResult = true;
	            }

	            if($addResult) {
	            	//Событие после добавления товара в корзину
					$resEvent = $this->es->callEvent('esOnAddToCart', array('hash'	=> $itemHash, 'cart' => $this->cart['items']));
		            if(is_array($resEvent)) {
		            	$this->cart['items'] = $resEvent['cart'];
		            }
	                return true;
	            }
	        } 	        
	        return false;
		}

		public function remove($hash) {
			if (array_key_exists($hash, $this->cart['items'])) {
				$resEvent = $this->es->callEvent('esOnBeforeRemoveFromCart', array('hash' => $hash, 'cart' => $this->cart['items']));
		        if(is_array($resEvent)) {
		           	$this->cart['items'] = $resEvent['cart'];
		        }

	            unset($this->cart['items'][$hash]);

	            $resEvent = $this->es->callEvent('esOnRemoveFromCart', array('hash' => $hash, 'cart' => $this->cart['items']));
		        if(is_array($resEvent)) {
		           	$this->cart['items'] = $resEvent['cart'];
		        }    
	            return true;
	        } else {
	            return false;
	        }
		}

		public function clean($callEvent=true){
			if($callEvent) {
				//Событие перед очисткой корзины
				$resEvent = $this->es->callEvent('esOnBeforeEmptyCart', array('cart' => $this->cart['items']));
	            if(is_array($resEvent)) {
	            	$this->cart['items'] = $resEvent['cart'];
	            }
			}

	        foreach ($this->cart['items'] as $hash => $item) {
	            unset($this->cart['items'][$hash]);
	        }
	        
	        $this->cart = ['items'=>[], 'currency'=>$this->es->currency];

	        if($callEvent) {
	        	//Событие после очистки корзины
				$resEvent = $this->es->callEvent('esOnEmptyCart', array('cart' => $this->cart['items']));
	            if(is_array($resEvent)) {
	            	$this->cart['items'] = $resEvent['cart'];
	            }
	      	}
	        return true;
		}

		public function change($hash, $quantity)
	    {
	        if (array_key_exists($hash, $this->cart['items'])) {
	            if ($quantity <= 0) {
	                return $this->remove($hash);
	            } else {
	            	//Событие перед изменением количества товара в корзине
					$resEvent = $this->es->callEvent('esOnBeforeChangeInCart', array('hash' => $hash, 'quantity' => $quantity, 'cart' => $this->cart['items']));
		            if(is_array($resEvent)) {
		            	$hash = $resEvent['hash'];
		            	$quantity = $resEvent['quantity'];
		            	$this->cart['items'] = $resEvent['cart'];
		            }

	                $this->cart['items'][$hash]['quantity'] = $quantity;

	                //Событие после изменения количества товара в корзине
					$resEvent = $this->es->callEvent('esOnChangeInCart', array('hash' => $hash, 'quantity' => $quantity, 'cart' => $this->cart['items']));
		            if(is_array($resEvent)) {
		            	$hash = $resEvent['hash'];
		            	$quantity = $resEvent['quantity'];
		            	$this->cart['items'] = $resEvent['cart'];
		            }
	            }
	            return true;
	        } else {
	            return false;
	        }
	    }

}


?>