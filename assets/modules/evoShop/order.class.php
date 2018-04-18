<?php 
include_once(MODX_BASE_PATH . "assets/modules/evoShop/evoshop.class.php");

class Order
{  
		
		public function __construct ($modx) {
			$this->modx = $modx;
			$this->evoshop = evoShop::getInstance($modx);
			$this->order = $this->getOrder();
		}

		public function getOrder(){
			
		}

		//Геттер полей таблицы
		public function get($fieldName){
			
		}

		//Сеттер полей таблицы
		public function set($fieldName, $fieldValue){
			
		}

		public function add(){

		}

		public function submit(){
			
		}

		public function getCost(){
			
		}

		public function clean(){
			
		}

		public function save(){
			
		}

		public function changeStatus(){
			
		}

		public function history($order_id, $action = 'status', $entry){
			
		}



}


?>