<?php
use Helpers\Lexicon;
include_once(MODX_BASE_PATH . "assets/lib/MODxAPI/modResource.php");
require_once(MODX_BASE_PATH . "assets/snippets/DocLister/lib/DLTemplate.class.php");
require_once(MODX_BASE_PATH . "assets/snippets/FormLister/lib/Lexicon.php");

evoShop::setLang();
evoShop::setCurrency();

class evoShop
{
	
	    protected static $instance;
	    public static $lang;
	    public static $currency;
	    protected static $defaultLang = 'ru';
		protected static $defaultCurrency = 'RUB';

		public $cart;
		public $order;
		
		private function __construct ($modx) {
			$this->modx = $modx;

			$this->lang = self::$lang;
			$this->currency = self::$currency;
			$this->blang = $this->getBLangArray(); //Массив со всеми значениями таблицы с переводами	

			$this->config = $this->getConfig();
		}

		private function __clone () {}
		private function __wakeup () {}

		public static function getInstance()
	    {
	        if (null === self::$instance) {
	        	$modx = new DocumentParser;
				$modx->getSettings();
	            self::$instance = new self($modx);
	        }
	        return self::$instance;
	    }

	    public function init() {
	    	if (!class_exists('Cart')) {
	            require_once MODX_BASE_PATH . 'assets/modules/evoShop/cart.class.php';
	        }
	        if (!class_exists('Order')) {
	            require_once MODX_BASE_PATH . 'assets/modules/evoShop/order.class.php';
	        }
	        $this->cart = new Cart($this, $this->config);
	        $this->order = new Order($this, $this->config);
	    }

	    //Установка языка и валюты
	    public function setLang() {
			$_SESSION['lang'] = $_REQUEST['lang'] ? mb_strtolower($_REQUEST['lang']) : self::$defaultLang;
			
			$langDir = MODX_BASE_PATH . 'assets/modules/evoShop/lang/'.$_SESSION['lang'];
			if(!is_dir($langDir)) {
				$_SESSION['lang'] = self::$defaultLang;
			}
			self::$lang = $_SESSION['lang'];
		}

		public function setCurrency() {
			$_SESSION['currency'] = $_REQUEST['currency'] ? mb_strtoupper($_REQUEST['currency']) : $_SESSION['currency'] ?: self::$defaultCurrency;
			self::$currency = $_SESSION['currency'];
		}

		//Получение конфигурации
	    public function getConfig() {
		    $config_file = MODX_BASE_PATH . 'assets/modules/evoShop/config.json';
		    $configJson = file_get_contents($config_file);
   
   			$phs = [];
   			$phs = $this->blang;
   			$phs['lang'] = $this->lang;
   			$phs['currency'] = $this->currency;

		    $configJson = $this->modx->parseText($configJson, $phs, '[(__', ')]');

		    $config = json_decode($configJson, true);
		    $config['cart_url'] = $this->modx->getConfig('site_url').$this->lang.$this->modx->makeUrl($config['settings']['cart_doc_id']);

		    foreach($config['templates'] as $key=>$val) {
		    	$config['templates'][$key] = $this->getTemplate($key, $val, $config);
		    }
		    return $config;
		}
		
		//Шаблонизация
	    public function getTemplate($tplName, $chunkName, $config) {
	    	if(!$chunkName) {
	    		DLTemplate::getInstance($this->modx)->setTemplatePath('assets/modules/evoShop/tpl/');
				DLTemplate::getInstance($this->modx)->setTemplateExtension('tpl');		
	    		$chunkName = '@FILE:'.$tplName;
	    	}


	    	$chunkText = DLTemplate::getInstance($this->modx)->getChunk($chunkName);

	    	$chunkText = $this->modx->parseText($chunkText, $config, '[+', '+]');

	    	return str_replace(['[+','+]'], ['{+', '+}'], $this->modx->parseText($chunkText, $this->blang, '[(__', ')]'));
	    }


	    //Формирование массива параметров товара для отображения
	    public function getOptionsObject($opts) {
			$opt = [];
			if(isset($opts) && is_array($opts) && count($opts)>0) {
				$opt_names = implode('","', array_keys($opts));

				$result_query = $this->modx->db->select(
					'name,caption', 
					$this->modx->getFullTableName('site_tmplvars'), 
					'name IN ("'.$opt_names.'")', 
					'find_in_set(name, "'.implode(',', array_keys($opts)).'")'
				);

				if($this->modx->db->getRecordCount($result_query)>0) {
					while($optRow = $this->modx->db->getRow($result_query)) {
						$opt[$optRow['name']] = ['caption'=>$optRow['caption'], 'name'=>$optRow['name'], 'value'=>$opts[$optRow['name']]];
					}
				}
			}
			return $opt;
		}

		//Вспомогательный метод
		public function sanitize($str, $type=null) {
			switch ($type) {
				case 'num':
					return (float) str_replace([' ', ','], ['', '.'], trim($str));			
				break;

				default:
					return trim($str);
				break;
			}
		}

		//Пересчет цены товара в выбранную валюту
		public function toCurrency($price, $currency) {
			$productCurrencyRate = $this->config['currencies'][mb_strtoupper($currency)]['rate']; //Курс валюты цены товара
			$siteCurrencyRate = $this->config['currencies'][$this->currency]['rate']; //Курс валюты сайта	
			$rate = $productCurrencyRate / $siteCurrencyRate; // Курс-множитель
			return $this->sanitize(round($this->sanitize($price, 'num')*$rate, 2), 'num');
		}





		public function calcPrice($doc, $opts) {
			$price = $doc->get($this->config['generalTV']['price']); //Цена товара
			$currency = $doc->get($this->config['generalTV']['currency']); //Валюта цены товара

			if(is_array($opts)) {
				foreach ($opts as $k=>$v) {
					$paramArr = $this->optionsParseString($doc->get($k)); // select:Черный||Белый==+200

					if(is_array($v)) {
						foreach($v as $p) {
							$price = $this->priceCalculate($price, $paramArr['params'][$p]);
						}
					} else {
						$price = $this->priceCalculate($price, $paramArr['params'][$v]);
					}
					
				}
			}
			return $this->toCurrency($price, $currency);
		}

		//Получение цены товара по ID
		public function getPriceById($docId, $opts) {
			if(!$docId) return false;
			$doc = $this->getDoc($docId);
			return $this->calcPrice($doc, $opts);
		}

		public function getPriceByDoc($doc, $opts) {
			if(!is_object($doc)) return false;
			return $this->calcPrice($doc, $opts);
		}

		//Пересчет цены товара с выбранными параметрами
		public function priceCalculate ($price=0, $change='') {
			$symbol = preg_replace("/[,.0-9]/", '', $change);

			if(empty($change)) return $price;

			switch($symbol) {
				case '*':
					$newPrice = $price * preg_replace("/[^,.0-9]/", '', $change);
				break;

				case '+':
					$newPrice = $price + preg_replace("/[^,.0-9]/", '', $change);
				break;

				case '-':
					$newPrice = $price - preg_replace("/[^,.0-9]/", '', $change);
				break;

				default:
					$newPrice = preg_replace("/[^,.0-9]/", '', $change);
				break;
			}

			return $newPrice;
		}


		//Парсинг строки из ТВ, в котором находятся изменяемые параметры
		public static function optionsParseString($str){
			$out = [];
			list($optionType, $params) = explode(':',$str);
			$out['type']=$optionType;
			if(count($params)>0) {
				$arrParams = explode('||', $params);
				foreach($arrParams as $param) {
					list($field, $value) = explode('==', $param);
					$out['params'][$field] = $value;
				}
			}
			return $out;
		}

		//Форматирование цены
		public function price_format($price, $setSign=true) {
			$price = (float)$price;
			$curr = $this->config['currencies'][$this->currency];
			if($setSign) {
				return $curr['prefix'] . number_format($price, $curr['accuracy'], $curr['decimal'], $curr['delimiter']) . $curr['suffix'];
			} else {
				return number_format($price, $curr['accuracy'], $curr['decimal'], $curr['delimiter']);
			}
		}

		//Получение документа-товара
		public function getDoc(int $id) {
			$doc = new modResource($this->modx);
			return $doc->edit($id);
		}

		//Множественное число
		public function plural($n, $type='product') {
			$forms = $this->config['plural'][$type];
		  	return $n%10==1&&$n%100!=11?$forms[0]:($n%10>=2&&$n%10<=4&&($n%100<10||$n%100>=20)?$forms[1]:$forms[2]);
		}

		public function getBLangArray(){
			$ret = [];
			$table = $this->modx->getFullTableName('blang');
			$q = $this->modx->db->query("CHECK TABLE {$table}");
			if($this->modx->db->getRecordCount($q)==1) {
				$arr = $this->modx->db->makeArray($this->modx->db->select('*', $table));
				foreach($arr as $row) {
					$ret[$row['name']] = $row[$this->lang];
				}
				return $ret;
			} else {
				return false;
			}	
		}

		public function translate(String $str){
			$parseStr = $this->modx->parseText($str, $this->blang, '[%', '%]');
			return str_replace(['[%', '%]'], ['',''], $parseStr);
		}

		public function callEvent(string $name, $data=array()) {
			$evtOut = $this->modx->invokeEvent($name, ['data'=>$data, 'es'=>$this]);
			if (is_array($evtOut) && count($evtOut) > 0){
	            $tmp = array_pop($evtOut);
	        }
	        if(!empty($tmp)) {
	        	$unserialized = unserialize($tmp);
	        	return is_array($unserialized) ? $unserialized : false;
	        } 
		}
}