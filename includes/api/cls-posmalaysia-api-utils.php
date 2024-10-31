<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
class Api_Utils_PosMalaysia
{
	// --------------------------- server error ---------------------------

	function server_error()
	{
		$error['error'] = 'An unexpected error has occurred. If issue persists, please contact the Pos Malaysia support';
		return json_encode($error);
	}

	// --------------------------- setup shipping ---------------------------

	function has_setup_shipping_details()
	{
		$shopname = get_option('posmalaysia_sender_name');
		$publickey = get_option('posmalaysia_Apikey');
		$secretkey = get_option('posmalaysia_secret_key');

		if ($shopname && $publickey && $secretkey) {
			return true;
		}
		return false;
	}

	function no_shipping_details_error_msg()
	{
		$error['error'] = 'An error has occurred. Please check your Pos Malaysia settings in WooCommerce shipping account settings';
		return json_encode($error);
	}

	// --------------------------- register shop ---------------------------
	function get_billing_address()
	{
		$billingaddress = array(
			'phone' => get_option('posmalaysia_sender_phone'),
			'address1' => get_option('woocommerce_store_address'),
			'address2' => get_option('woocommerce_store_address_2'),
			'city' => get_option('woocommerce_store_city'),
			'zip' => get_option('woocommerce_store_postcode'),
			'province' => get_option('woocommerce_store_city'),
		);
		return $billingaddress;
	}

	function get_register_shop_request_body()
	{
		$account_no = get_option('PosAccountNo');
		$shop_name = get_option('posmalaysia_sender_name');

		$billing_address = self::get_billing_address();
		$reg_body = array(
			'accountNo'  => $account_no,
			'publicKey' => get_option('posmalaysia_Apikey'),
			'companyName' => get_option('PosCompanyname'),
			'shopName' => $shop_name,
			'storeName' => $shop_name,
			'billingAddress' => $billing_address,
		);
		
		$subscription_status = get_option('wc_api_subscription_status');
	
		if ($subscription_status && $subscription_status != 'done') {
			$woocommerce_API = new posmalaysia_woocommerce_API(false);
			$wc_api_info = $woocommerce_API->get_info($account_no, $shop_name);
			$reg_body = array_merge($reg_body, $wc_api_info);
			update_option('wc_api_subscription_status', 'processing');
		}

		return $reg_body;
	}

	// --------------------------- token ---------------------------

	public function get_access_token_request_body()
	{
		$shopname = get_option('posmalaysia_sender_name');
		$publickey = get_option('posmalaysia_Apikey');
		$secretkey = get_option('posmalaysia_secret_key');
		// $secretkey = get_option('posmalaysia_secret_key') . 'abcd';

		$authkey = hash('sha256', $publickey . '.' . $shopname . '.' . $secretkey);

		$auth = array(
			'shopName' => $shopname,
			'publicKey' => $publickey,
			'authKey' => $authkey,
		);
		return self::get_json_string($auth);
	}

	// --------------------------- common function ---------------------------

	public static function get_json_string($data)
	{
		foreach (array('data'	=> json_encode($data)) as $key => $value) {
		}
		return $value;
	}

	function remove_url_query($url, $key)
	{
		$url = preg_replace('/(?:&|(\?))' . $key . '=[^&]*(?(1)&|)?/i', "$1", $url);
		$url = rtrim($url, '?');
		$url = rtrim($url, '&');
		return $url;
	}

	function remove_query($redirect_to)
	{
		$redirect_to = self::remove_url_query($redirect_to, 'msg');
		$redirect_to = self::remove_url_query($redirect_to, 'acti');
		return $redirect_to;
	}

	public static function add_space($text)
	{
		return preg_replace('/(?<!\ )[A-Z]/', ' $0', $text);
	}

	public static function get_err_msg($key, $msg)
	{
		$err_msg = $key . ' ' . str_replace('Invalid value, value ', '', $msg);
		$err_msg = self::add_space($err_msg);
		$err_msg = strtolower($err_msg);
		return $err_msg;
	}

	public static function get_full_url($path)
	{
		//production
		 return 'https://ecom-pi-svc.pos.com.my' . $path;

		//staging
		 //return 'https://ecom-pi-svc.uat-pos.com' . $path;
		//return 'http://localhost:5151' . $path;
	}
}