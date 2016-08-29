<?php

class VideoProcessor extends Magmi_ItemProcessor {

	protected $_videocol = array();


	public function getPluginInfo()
	{
		return array(
			'name'=>'Video Importer',
			'author'=>'Andreas Bilz,herooutoftime',
			'version'=>'0.0.1'
		);
	}

	public function processItemAfterId(&$item, $params = null)
	{
		$pid = $params["product_id"];
		$cpn = $this->tablename($this->getParam("CUSTPRI:table", "customerprices_prices"));
		$videocol = array_intersect(array_keys($this->_videocol), array_keys($item));

		// Do nothing if item has no customer price info or has not change
		if (count($videocol) == 0)
			return true;

		var_dump($videocol);
		// Get all Mage-customers by their wawi-customer-id
		// CAUTION: Multiple Mage-customers possible
		$customers = $this->getCustomers();

		// Remove previously created entries
		$sql = "DELETE FROM {$cpn} WHERE product_id = {$pid}";
		$this->delete($sql);

		// Prepare basic SQL-query
		$sql = "INSERT INTO $cpn
			(customer_id, product_id, store_id, qty, price, special_price, customer_email, created_at, updated_at) VALUES (:customer_id, :product_id, :store_id, :qty, :price, :special_price, :customer_email, :created_at, :updated_at)";

		foreach ($videocol as $k) {
			// get customer price column info
			$cpinf = $this->_videocol[$k];
			$_wawi_knr = $cpinf['name'];
			foreach ($customers[$_wawi_knr] as $customer) {
				$price = str_replace(",", ".", $item[$k]);
				$special_price = str_replace(",", ".", $item[$k]);
				$data = array(
					'customer_id' => $customer['id'],
					'product_id' => $pid,
					'store_id' => '0',
					'qty' => 1,
					'price' => $price,
					'special_price' => $special_price,
					'customer_email' => $customer['email'],
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				);
				// Execute SQL-query
				$this->insert($sql, $data);
			}
		}
		return true;
	}

	public function processColumnList(&$cols, $params = null)
	{
		$pattern = $this->getParam("VIDEO:column_name", "video");
		foreach ($cols as $col) {
			if (preg_match("|{$pattern}|", $col, $matches)) {
				$tpinf = array("name" => $matches[1], "id" => null);
				$this->_videocol[$col] = $tpinf;
			}
		}
		return true;
	}

	public function initialize($params)
	{

	}
}