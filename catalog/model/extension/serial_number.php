<?php

class ModelExtensionSerialNumber extends Model
{
	public function getOrderProductSerials($product_id)
	{
		return $this->db->query("SELECT serial FROM ".DB_PREFIX."order_product_serial_numbers WHERE order_product_id = '".$product_id."'"  )->rows;
	}

	public function getProductSerialsByOrderProduct($order_product_id)
	{

		$product = $this->db->query("SELECT product_id FROM ".DB_PREFIX."order_product WHERE order_product_id = $order_product_id")->row;

		return  $this->db->query("SELECT distinct psn.serial FROM ".DB_PREFIX."order o JOIN ".DB_PREFIX."order_product op ON(o.order_id = op.order_id) JOIN ".DB_PREFIX."product_serial_numbers psn ON(op.product_id = psn.product_id) WHERE psn.product_id = ".$product['product_id']."")->rows;
	}

	public function addOrderProductSerials($product_id, $product_serial)
	{
		return $this->db->query("INSERT INTO ".DB_PREFIX."order_product_serial_numbers SET order_product_id = '".$product_id."', serial = '".$product_serial."'");
	}

	public function deleteProductSerialsByOrder($serial)
	{
		return $this->db->query("DELETE FROM ".DB_PREFIX."product_serial_numbers WHERE serial = '".$serial."'");
	}

	public function getOrdersByProductSerial($product_serial, $customer_id) {
		$query = $this->db->query("SELECT o.order_id, o.firstname, o.lastname, os.name as status, o.date_added, o.total, o.currency_code, o.currency_value FROM " . DB_PREFIX . "order_product_serial_numbers opsn JOIN ".DB_PREFIX."order_product op ON(opsn.order_product_id = op.order_product_id) JOIN ".DB_PREFIX."order o ON(op.order_id = o.order_id) LEFT JOIN " . DB_PREFIX . "order_status os ON (o.order_status_id = os.order_status_id) WHERE o.customer_id = '".$customer_id."' AND opsn.serial = '".$product_serial."' LIMIT 1");

		return $query->rows;
	}

	
	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->row['total'];
	}

}