<?php  if ( ! defined( 'ABSPATH' ) ) exit; 

class posmalaysia_Helper
{
	public $tracking_api_posmalaysia = null;
	public $auth_api_posmalaysia = null;
	public $print_api_posmalaysia = null;
	public $preacceptance_api_posmalaysia = null;
	public $status_api_posmalaysia = null;
	public $woocommerce_API = null;


	public function __construct()
	{
		$this->tracking_api_posmalaysia = new Tracking_Api_PosMalaysia();
		$this->auth_api_posmalaysia = new Auth_Api_PosMalaysia();
		$this->print_api_posmalaysia = new Print_Api_PosMalaysia();
		$this->preacceptance_api_posmalaysia = new Preacceptance_Api_PosMalaysia();
		$this->status_api_posmalaysia = new Status_Api_PosMalaysia();
		$this->woocommerce_API = new posmalaysia_woocommerce_API(false);
	}

	public function register($registration_detail)
	{
		$account_no = $registration_detail['accountno'];
		$email =  $registration_detail['email'];
		$phone_no = $registration_detail['phoneno'];
		$store_name = $registration_detail['storename'];
		$requestor_name = $registration_detail['requestor_name'];

		$reg_data = array(
			'accountNo'	=> $account_no,
			'channel' => 'WooCommerce',
			'shopName'	=> $store_name,
			'email'		=> $email,
			'name' => $requestor_name,
			'phone'	=> $phone_no,
		);

		$this->woocommerce_API->_generate_api_key($account_no, $store_name);
		$wc_api_info = $this->woocommerce_API->get_info($account_no, $store_name);
		$reg_data = array_merge($reg_data, $wc_api_info);
		update_option('wc_api_subscription_status', 'processing');

		return $this->auth_api_posmalaysia->validation($reg_data);
	}

	public function tracking($trackingdata)
	{
		$trackdata = array(
			'methodcode'	=> "wc_tracking",
			'connoteno' 	=> $trackingdata
		);
		return $this->tracking_api_posmalaysia->tracking($trackdata);;
	}

	public function process_print($ids)
	{
		$items = array();
		foreach ($ids as $id) {
			$order = wc_get_order($id);
			if ($order->get_shipping_phone()) {
				$receiverphone = $order->get_shipping_phone();
			} else if ($order->get_shipping_phone() && strpos($order->get_shipping_phone(), '/') !== false) {
				$receiverphone = explode("/", $order->get_shipping_phone());
				$receiverphone = $receiverphone[0];
			} else if (strpos($order->get_billing_phone(), '/') !== false) {
				$receiverphone = explode("/", $order->get_billing_phone());
				$receiverphone = $receiverphone[0];
			} else {
				$receiverphone = $order->get_billing_phone();
			}

			$weight_unit = get_option('woocommerce_weight_unit');
			$kg = 1000;
			$weight = 0;
			$price = 0;
			$item_name = '';
			$productItems = array();
			if (sizeof($order->get_items()) > 0) {
				foreach ($order->get_items() as $item) {
					if ($item['product_id'] > 0) {
						$_product = $order->get_product_from_item($item);
						if (!$_product->is_virtual()) {
							if (is_numeric($item['qty'])) {
								if (is_numeric($_product->get_weight())) {
									$weight += ($_product->get_weight() * $item['qty']);
								}
							}
							$item_name .= $item['qty'] . 'x ' . $item['name'] . ', ';
							$productItem = array(
								"name" => $item['name'],
								"qty" =>  $item['qty']
							);
							array_push($productItems, $productItem);
						}
					}
				}
			}
			$price = $order->get_total();

			if ($weight == '0') {
				$weight = 0.1;
			} else {
				if ($weight_unit == 'kg') {
					$weight = $weight;
				} else if ($weight_unit == 'g') {
					$weight = $weight / $kg;
					if ($weight <= 0.01) {
						$weight = 0.01;
					}
				}
			}

			if (!get_post_meta($id, '_plorder', true)) {
				$orderid = date('ymd') . str_pad($id, 6, 0, STR_PAD_LEFT);
			} else {
				$orderid = get_post_meta($id, '_plorder', true);
			}

			$item = array(
				"id" => $id,
				"currency" => "MYR",
				"orderID" => $orderid,

				"receiverAddress" => $order->shipping_address_1 . ', ' . $order->shipping_address_2,
				"receiverCity" => $order->shipping_city,
				"receiverCompanyName" => $order->get_formatted_shipping_full_name(),
				"receiverCountryCode" => "MY",
				"receiverName" => $order->get_formatted_shipping_full_name(),
				"receiverPhone" => preg_replace('/\s+/', '', $receiverphone),
				"receiverPostcode" => $order->get_shipping_postcode(),
				"receiverState" => $order->shipping_state,

				"totalPrice" => $price,
				"totalWeight" => round($weight, 2),

				"isCod" => $order->payment_method == 'cod',

				"connoteNo" => get_post_meta($id, 'plconnote', true),
				"delbit" => get_post_meta($id, '_delbit', true),
				"routing" => get_post_meta($id, '_routing', true),
				"items" => $productItems
			);

			array_push($items, $item);
		}
		
		return $this->print_api_posmalaysia->print_label($items);
	}


	///--------------------------preacceptance--------------------------------------------------------------

	public function preacceptance_dropoff($ids)
	{
		$connote_nos = array();
		foreach ($ids as $id) {
			$connote_no = get_post_meta($id, 'plconnote', true);
			array_push($connote_nos, $connote_no);
		}
		$dropoff_data['connoteNos'] = $connote_nos;
		return $this->preacceptance_api_posmalaysia->dropoff($dropoff_data);
	}

	///--------------------------------------pickup--------------------------------------------------------------------------------------

	public function preacceptance_pickup($ids)
	{
		$connote_nos = array();
		foreach ($ids as $id) {
			$connote_no = get_post_meta($id, 'plconnote', true);
			array_push($connote_nos, $connote_no);
		}
		$dt = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
		$selectedDate = $dt->format("Y-m-d");
		$selectedTime = $dt->format("h:i A");
		$pickup_data['connoteNos'] = $connote_nos;
		$pickup_data['pickupDate'] = $selectedDate;
		$pickup_data['pickupTime'] = $selectedTime;
		return $this->preacceptance_api_posmalaysia->pickup($pickup_data);
	}

	///--------------------------status--------------------------------------------------------------

	public function get_orders_status($ids)
	{
		$connote_nos = array();
		foreach ($ids as $id) {
			$connote_no = get_post_meta($id, 'plconnote', true);
			array_push($connote_nos, $connote_no);
		}
		return $this->status_api_posmalaysia->status($connote_nos);
	}

	///--------------------------helper--------------------------------------------------------------

	function get_json_string($data)
	{
		foreach (array('data'	=> json_encode($data)) as $key => $value) {
		}
		return $value;
	}
}