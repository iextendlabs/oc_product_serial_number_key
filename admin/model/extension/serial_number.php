<?php

class ModelExtensionSerialNumber extends Model
{

	private function write($message) {

        fwrite(fopen(DIR_LOGS . 'serials.log', 'a'), date('Y-m-d G:i:s') . ' - ' . print_r($message, true) . "\n");
    }

    public function getOrderProductName($order_product_id)
    {
    	$query = $this->db->query("SELECT name FROM ".DB_PREFIX."order_product WHERE order_product_id = '".$order_product_id."'");

    	if($query->num_rows){
    		return $query->row['name'];
    	}
    }

	public function restoreProductSerials($order_product_id, $order_id){
		
		$serials = $this->db->query("SELECT serial FROM ".DB_PREFIX."order_product_serial_numbers WHERE found = 1 and order_product_id=".$order_product_id);

		// get order product all serials
		$products_serials = $this->db->query("SELECT serial FROM ".DB_PREFIX."order_product_serial_numbers WHERE order_product_id = ".$order_product_id);

		$product_id = $this->db->query("SELECT * FROM ".DB_PREFIX."order_product WHERE order_product_id=".$order_product_id)->row['product_id'];

		foreach ($serials->rows as $value) {
			$this->db->query("INSERT into ".DB_PREFIX."product_serial_numbers set serial= '".$value['serial']."', product_id=".$product_id);
		}

		$query = $this->db->query("SELECT name FROM ".DB_PREFIX."order_product WHERE order_product_id = '".$order_product_id."'");

    	if($query->num_rows){
    		$product_name =  $query->row['name'];
    	}

		foreach ($products_serials->rows as $value) {
            $this->write('Product('.$product_name.') serial "'.$value['serial'].'" is removed from Order# '.$order_id );
		}

		$this->db->query("DELETE  FROM ".DB_PREFIX."order_product_serial_numbers WHERE order_product_id=".$order_product_id);
	}

	public function getOrderProducts($order_id) 
	{
		return $this->db->query("SELECT op.*, p.require_serial FROM " . DB_PREFIX . "order_product op JOIN ".DB_PREFIX."product p ON(op.product_id = p.product_id) WHERE op.order_id = '" . $order_id . "'")->rows;
	}


	public function getProductSerials($product_id)
	{
		return $this->db->query("SELECT serial FROM ".DB_PREFIX."product_serial_numbers WHERE product_id = '".$product_id."'"  )->rows;
	}


	public function getProductSerialsByOrderProduct($order_product_id)
	{
		$product = $this->db->query("SELECT product_id FROM ".DB_PREFIX."order_product WHERE order_product_id = $order_product_id")->row;

		return $this->db->query("SELECT distinct psn.serial FROM ".DB_PREFIX."order o JOIN ".DB_PREFIX."order_product op ON(o.order_id = op.order_id) JOIN ".DB_PREFIX."product_serial_numbers psn ON(op.product_id = psn.product_id) WHERE psn.product_id = ".$product['product_id']."")->rows;
	}


	public function getOrdersByProductSerial($serial)
	{
		return $this->db->query("SELECT opsn.*, op.*, o.*,oh.*,os.name AS order_status FROM ".DB_PREFIX."order_product_serial_numbers opsn JOIN ".DB_PREFIX."order_product op ON (opsn.order_product_id = op.order_product_id) JOIN ".DB_PREFIX."order o ON(op.order_id = o.order_id) JOIN ".DB_PREFIX."order_history oh ON(o.order_id = oh.order_id) JOIN ".DB_PREFIX."order_status os ON(oh.order_status_id = os.order_status_id) WHERE opsn.serial = '".$serial."' ORDER BY o.order_id LIMIT 1")->rows;
	}

	public function addProductSerials($product_id, $product_serial)
	{
		return $this->db->query("INSERT INTO ".DB_PREFIX."product_serial_numbers SET product_id = '".$product_id."', serial = '".$product_serial."'");
	}


	public function deleteProductSerials($product_id)
	{
		return $this->db->query("DELETE FROM ".DB_PREFIX."product_serial_numbers WHERE product_id = '".$product_id."'");
	}



	public function addOrderProductSerials($product_id, $product_serial, $found)
	{
		return $this->db->query("INSERT INTO ".DB_PREFIX."order_product_serial_numbers SET order_product_id = '".$product_id."', serial = '".$product_serial."' , found = '".$found."'");
	}



	public function getOrderProductSerials($product_id)
	{
		return $this->db->query("SELECT serial FROM ".DB_PREFIX."order_product_serial_numbers WHERE order_product_id = '".$product_id."'"  )->rows;
	}

	public function fetchOrderProductSerials($product_id)
	{
		return $this->db->query("SELECT serial FROM ".DB_PREFIX."order_product_serial_numbers WHERE order_product_id = '".$product_id."' AND found = 1")->rows;
	}


	public function deleteOrderProductSerials($product_id)
	{
		return $this->db->query("DELETE FROM ".DB_PREFIX."order_product_serial_numbers WHERE order_product_id = '".$product_id."'");
	}

	public function deleteProductSerialsByOrder($serial)
	{
		return $this->db->query("DELETE FROM ".DB_PREFIX."product_serial_numbers WHERE serial = '".$serial."'");
	}

}