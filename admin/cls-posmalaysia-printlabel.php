<?php   if ( ! defined( 'ABSPATH' ) ) exit; 

class posmalaysia_printlabel
{

	public $posmalaysia_helper = null;
	public $api_utils = null;

	public function __construct()
	{	
		
		$this->posmalaysia_helper = new posmalaysia_Helper();
		$this->api_utils = new Api_Utils_PosMalaysia();

		$this->pickup_api =new  posmalaysia_preacceptancepickup();
		
		$this->define_hooks();
		if (isset($_GET['acti']) && $_GET['acti'] == 'print') {
			add_action('admin_notices', [$this, 'print_notice']);
			add_action('admin_head', [$this,'open_consignment_pdf']);
		}
	}
	
	public function open_consignment_pdf(){ ?>
		<script>window.open('<?php echo $_GET["msg"]?>', "_blank")</script>
	<?php
	}

	/**
	 * Define hooks
	 */
	protected function define_hooks()
	{
		add_filter('bulk_actions-edit-shop_order', [$this, 'bulk_actions_printlabel'], 30);
		add_filter('handle_bulk_actions-edit-shop_order', [$this, 'handle_bulk_action_printlabel'], 10, 3);
	}

	public function bulk_actions_printlabel($actions)
	{

		$actions['posmalaysia_printlabel'] = __('Pos Malaysia Create Consignment and Pick Up');

		return $actions;
	}

	public function print_notice()
	{
		$class = 'notice notice-success is-dismissible';
		$message = $_GET['msg'];
		printf('<div class="%1$s"><p>Pos Malaysia consignment note successfully generated. Click <a href="%2$s" target="_blank">here</a> to download.</p></div>', esc_attr($class), esc_html($message));
	}

	public function trim_string($str, $limit = 1000, $strip = false)
	{
		$str = ($strip == true) ? strip_tags($str) : $str;
		if (strlen($str) > $limit) {
			$str = substr($str, 0, $limit - 3);
			return (substr($str, 0, strrpos($str, ' ')) . '...');
		}
		return trim($str);
	}

	public function handle_bulk_action_printlabel($redirect_to, $action, $post_ids)
	{
	
		if ($action !== 'posmalaysia_printlabel') {
			return $redirect_to;
		}

		$processed_ids = array();
		if (count($post_ids) > 150) {
			$redirect_to = add_query_arg(array(
				'acti'	=> 'err',
				'msg'	=> "You can only select up to 150 orders to proceed."
			), $redirect_to);
			return $redirect_to;
		}
		foreach ($post_ids as $post_id) {
			$processed_ids[] = $post_id;
			$status=get_post_meta($post_id, 'plprocess', true);
		}

		$result = $this->posmalaysia_helper->process_print($processed_ids);
			
		$error = $result['error'];
		$data = $result['data'];
		$message = $result['message'];
		$connote_nos = null;

		if ($data) {
			$connote_nos = $data['connotes'];
		}
		if (!$error && $connote_nos != null && sizeof($connote_nos) > 0) {
			foreach ($connote_nos as $connoteno) {
				$orderid = $connoteno['orderID'];
				$plconnote = $connoteno['connoteNo'];
				$routing = $connoteno['routing'];
				$delbit = $connoteno['delbit'];
				$id = substr($orderid, strlen($orderid) - 6);
				$id = ltrim($id, '0');
				update_post_meta($id, 'plconnote', $plconnote);
				update_post_meta($id, '_plorder', $orderid);
				update_post_meta($id, '_delbit', $delbit);
				update_post_meta($id, '_routing', $routing);
			}
			$pdf = $data['pdf'];
			if (!filter_var($pdf, FILTER_VALIDATE_URL)) {
				$redirect_to = add_query_arg(array(
					'acti'	=> 'err',
					'msg'	=> $pdf
				), $redirect_to);
				return $redirect_to;
			}

			$action='posmalaysia_preacceptancepickup';
		
			$pickupredirect_to =$this->handle_action_preacceptancepickup($redirect_to, $action, $processed_ids,$pdf);
			
			parse_str( parse_url( $pickupredirect_to, PHP_URL_QUERY), $redirectpickuparr );
		
			if($redirectpickuparr['acti']=='err'){
				
				$redirect_to = $this->api_utils->remove_query($redirect_to);
			
				$redirect_to = add_query_arg(array(
					'acti'    => 'err',
					'msg'    => $redirectpickuparr['msg']
				), $redirect_to);
				
				return $redirect_to;
			}
			else{
				$redirect_to = $this->api_utils->remove_query($redirect_to);
				$redirect_to = add_query_arg(array(
					'acti'    => 'print',
					'msg'    => $pdf
				), $redirect_to);
				
				return $redirect_to;
			}
			
		} else {
			$msg = null;
			if ($error) {
				if (is_array($error)) {
					$orderid = $result['id'];
					$keys = array_keys((array)$error);
					foreach ($keys as $key) {
						$err_msg = Api_Utils_PosMalaysia::get_err_msg($key, $error[$key]);
						$msg .= 'Order Id ' . $orderid . ' ' . $err_msg . ' ';
					}
				} else {
					$msg = $error;
				}
			} else if (!empty($message)) {
				$msg = $message;
			}

			if (!$msg) {
				$msg = 'Print label error';
			}
			$redirect_to = add_query_arg(array(
				'acti'	=> 'err',
				'msg'	=> $msg
			), $redirect_to);
			return $redirect_to;
		}
	}
	public function handle_action_preacceptancepickup($redirect_to, $action, $processed_ids,$pdf){

		$redirect_to=$this->pickup_api->handle_bulk_action_preacceptancepickup($redirect_to, $action, $processed_ids);
		
		return $redirect_to;
		
	}
	
	static function get_json_string($data)
	{
		foreach (array('data'	=> json_encode($data)) as $key => $value) {
		}
		return $value;
	}
}