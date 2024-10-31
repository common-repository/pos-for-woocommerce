<?php  if ( ! defined( 'ABSPATH' ) ) exit; 

class posmalaysia_preacceptancedropoff
{
	public $posmalaysia_helper = null;
	public $api_utils = null;

	public function __construct()
	{
		$this->posmalaysia_helper = new posmalaysia_Helper();
		$this->api_utils = new Api_Utils_PosMalaysia();
		$this->define_hooks();
	}

	protected function define_hooks()
	{
		add_filter('bulk_actions-edit-shop_order', [$this, 'bulk_actions_preacceptancedropoff'], 30);
		add_filter('handle_bulk_actions-edit-shop_order', [$this, 'handle_bulk_action_preacceptancedropoff'], 10, 3);
	}

	public function bulk_actions_preacceptancedropoff($actions)
	{

		$actions['posmalaysia_preacceptancedropoff'] = __('Pos Malaysia Drop Off');

		return $actions;
	}

	public function handle_bulk_action_preacceptancedropoff($redirect_to, $action, $post_ids)
	{
		if ($action !== 'posmalaysia_preacceptancedropoff') {
			return $redirect_to;
		}
		$processed_ids = array();
		$empty_connote = array();
		$donepreaccept = array();
		$cod_connote = array();
		$selectedidcount = count($post_ids);

		if ($selectedidcount > 0) {
			foreach ($post_ids as $post_id) {
				$order = wc_get_order($post_id);

				if (!get_post_meta($post_id, 'plconnote', true)) {
					$empty_connote[] = $post_id;
				}
				if ($order->payment_method == 'cod') {
					$cod_connote[] = $post_id;
				} else if (get_post_meta($post_id, 'plpreacceptanceid', true)) {
					$donepreaccept[] = $post_id;
				} else {
					$processed_ids[] = $post_id;
				}
			}

			if (!empty($cod_connote)) {
				$redirect_to = add_query_arg(array(
					'acti' => 'err',
					'msg' => 'Cash on Delivery item is not supported for Drop Off Request',
				), $redirect_to);
				return $redirect_to;
			} else if (!empty($empty_connote)) {
				$redirect_to = add_query_arg(array(
					'acti' => 'err',
					'msg' => 'Please print label for the selected order before drop off',
				), $redirect_to);
				return $redirect_to;
			} else if (!empty($donepreaccept)) {
				$redirect_to = add_query_arg(array(
					'acti' => 'err',
					'msg' => 'Selected order has been set to drop off',
				), $redirect_to);
				return $redirect_to;
			} else {
				$result = $this->posmalaysia_helper->preacceptance_dropoff($processed_ids);
				$connotes = $result['data'];
				if ($connotes != null && sizeof($connotes) > 0) {
					for ($i = 0; $i < sizeof($connotes); $i++) {
						$preacceptance_transid = $connotes[$i]['preacceptanceID'];
						$id = $processed_ids[$i];
						add_post_meta($id, 'plpreacceptanceid', $preacceptance_transid, true);
						add_post_meta($id, 'plprocess', 'DROP OFF SCHEDULED', true);
					}

					$redirect_to = $this->api_utils->remove_query($redirect_to);
					return $redirect_to;
				} else {
					$error = $result['error'];
					$msg = 'Failed request process.';
					if ($error) {
						$msg = $error;
					}

					$redirect_to = add_query_arg(array('acti'	=> 'err', 'msg'	=> $msg), $redirect_to);
					return $redirect_to;
				}
			}
		} else {
			$redirect_to = add_query_arg(array(
				'acti'	=> 'err',
				'msg'	=> 'Please select at least 1 or more orders to drop off'
			), $redirect_to);
			return $redirect_to;
		}
	}

	static function get_json_string($data)
	{
		foreach (array('data'	=> json_encode($data)) as $key => $value) {
		}
		return $value;
	}
}