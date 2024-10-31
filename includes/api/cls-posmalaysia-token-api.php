<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
class Token_Api_PosMalaysia
{

	public $api_utils = null;
	public $auth_api = null;

	public function __construct()
	{
		$this->api_utils = new Api_Utils_PosMalaysia();
		$this->auth_api = new Auth_Api_PosMalaysia();
	}

	public function get_bearer_token()
	{
		$this->auth_api->register_shop();
		$result = self::get_token_response();
		return $result;
	}

	function get_token_response()
	{
		$body = $this->api_utils->get_access_token_request_body();
		$url = Api_Utils_PosMalaysia::get_full_url('/v2/auth');
		$header = array('Content-Type' => 'application/json');
		$response = wp_remote_post($url, array('headers' => $header, 'body' => $body, 'timeout' => 60,));

		$responseCode = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		$body = json_decode($body, true);

		if ($responseCode == 200) {
			$body['status'] = 'success';
		} else {
			$body['status'] = 'fail';
		}
		return $body;
	}
}