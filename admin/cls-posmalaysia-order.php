<?php  if ( ! defined( 'ABSPATH' ) ) exit; 

class posmalaysia_Order
{

	private $posmalaysia_helper = null;
	private $print_api = null;
	public $api_utils = null;
	private $limit = 50;
	private $status_colors = [
		'NO TRACKING ID GENERATED' => '#8F8F8F',
		'PENDING PICK UP/DROP OFF' => '#4B40A1',
		'PICK UP SCHEDULED' => '#7367CA',
		'DROP OFF SCHEDULED' => '#7367CA',
		'Pick Up' => '#7367CA',
		'Drop Off' => '#7367CA',
		'PICKED UP' => '#21AFFF',
		'DROPPED OFF' => '#057c38',
		'IN TRANSIT' => '#ff9c00',
		'OUT FOR DELIVERY' => '#2ECCCC',
		'TO COLLECT' => '#2ECCCC',
		'DELIVERED' => '#00C353',
		'FAILED' => '#FF0000',
		'RETURN TO SENDER' => '#0A097A',
		'RETURN SUCCESS' => '#2521FF'
	];

	public function __construct($define_hooks = true)
	{
		update_option('pos_last_triggered_action', '');
		$this->posmalaysia_helper = new posmalaysia_Helper();
		
		$this->api_utils = new Api_Utils_PosMalaysia();
		if ($define_hooks) $this->define_hooks();
		
	}

	/**
	 * Define hooks
	 */
	protected function define_hooks()
	{
		// Add a bulk action option to get pos order status
		add_filter('bulk_actions-edit-shop_order', [$this, 'bulk_actions_order_status'], 30);
		add_filter('handle_bulk_actions-edit-shop_order', [$this, 'handle_bulk_action_order_status'], 10, 3);

		// Display an action button to get pos order status
		add_action('manage_posts_extra_tablenav', [$this, 'admin_order_list_top_bar_button'], 20, 1);
		add_action('admin_enqueue_scripts', [$this, 'pos_status_enqueue']);
		add_action('wp_ajax_get_pos_status', [$this, 'get_pos_status']);
	
		add_filter('manage_edit-shop_order_columns', [$this, 'table_order_number_column_header']);
		add_action('manage_shop_order_posts_custom_column', [$this, 'table_order_number_column_content'], 10, 2);
		add_filter('woocommerce_shop_order_search_fields', [$this, 'connote_searchable_field'], 10, 1);
		add_action( 'admin_head', [$this,'generate_connote'],10,1);
		
			add_action( 'woocommerce_thankyou', [$this, 'call_connote_pickup_req'], 10,1 );
		add_action( 'init',  [$this,'register_pos_custom_statuses']);
		add_filter( 'wc_order_statuses', array( $this, 'add_pos_custom_statuses' ),10,1 );
	}

public function call_connote_pickup_req($order){
	$autoconnote=get_option('posmalaysia_auto_connote');
	if($autoconnote=='yes'){
		//posmalaysia_auto_connote
		$this->print_api= new posmalaysia_printlabel();
		$this->print_api->handle_bulk_action_printlabel($redirect_to='', 'posmalaysia_printlabel', array($order));
	}
		//$result = $this->posmalaysia_helper->process_print(array($order));	
		 
	 }
	public function table_order_number_column_header($columns)
	{
		$columns['plconnote'] = 'Pos Malaysia Tracking No.';
		$columns['pl_status'] = 'Pos Malaysia Status';
		return $columns;
	}

	public function table_order_number_column_content($columns, $post_id)
	{
		$track_url = 'https://tracking.pos.com.my/tracking/';
		$plconnote = esc_html(get_post_meta($post_id, 'plconnote', true));
		$status = esc_html(get_post_meta($post_id, 'pl_status', true));
		$process = esc_html(get_post_meta($post_id, 'plprocess', true));
	
			
			
		switch ($columns) {
			case 'plconnote':
				if(!empty($process))
				echo  "<a target='_blank' rel='noopener noreferrer' href='".esc_url($track_url.$plconnote)."'><u> ".esc_html($plconnote)." </u></a>";
				break;

			case 'pl_status':
				$output = '';

				if (empty($plconnote)) {
					$output = esc_html('NO TRACKING ID GENERATED');
				} elseif (empty($process)) {
					//$output =  'PENDING PICK UP/DROP OFF';
					$output = esc_html('NO TRACKING ID GENERATED');
				} elseif (empty($status)) {
					$output = esc_html($process);
				} else {
					$output = esc_html($status);
				}

				$color = $this->status_colors[$output] ?? '';
				$output = "<p style='color:$color'>" . esc_html($output);

				if (!empty($status)) {
					$tip = 'Updated on ' . esc_html(get_post_meta($post_id, 'pl_status_date', true));
					$output .= ' <span class="order-status-tooltip" data-tip="' . esc_html($tip) . '"></span>';
				}
				$output .= '</p>';
				echo $output;
				break;
				
			case 'order_status':		
				$status=get_post_meta($post_id, 'pl_status', true);
				if(empty($status)){
					$status=get_post_meta($post_id, 'plprocess', true);
				}
				if(!empty($status)){
					$order_status=$this->order_status_update();
					$order_status_val=$order_status[$status];
					$order = new WC_Order($post_id);
					$order->update_status($order_status_val);
				}
			
				break;
		}
	}

	public function connote_searchable_field($meta_keys)
	{
		$meta_keys[] = 'plconnote';
		return $meta_keys;
	}

	public function bulk_actions_order_status($actions)
	{
		$actions['posmalaysia_order_status'] = __('Get Pos Malaysia Status');
		return $actions;
	}

	public function handle_bulk_action_order_status($redirect_to, $action, $post_ids)
	{
		if ($action !== 'posmalaysia_order_status') {
			return $redirect_to;
		}

		$result = $this->update_pos_status_meta_field($post_ids, 'button');
		$status = $result[0];
		$msg = $result[1];

		if ($status == 'Success') {
			$redirect_to = add_query_arg(array(
				'acti'    => 'success',
				'msg'    => $msg,
				'reloaded'    => '1'
			), $redirect_to);
		} else {
			$redirect_to = add_query_arg(array(
				'acti'    => 'err',
				'msg'    => $msg,
				'reloaded'    => '1'
			), $redirect_to);
		}

		return $redirect_to;
	}


	function admin_order_list_top_bar_button($which)
	{
		global $pagenow, $typenow;

		if ('shop_order' === $typenow && 'edit.php' === $pagenow && 'top' === $which) {
?>
			<div class="alignleft actions custom">
				<!--<button type="submit" name="get_pos_status" id="get_pos_status" style="height:32px;" class="button" value="yes">
				Generate Connote
				</button>-->
				
				<button type="submit" name="gen_connote" id="gen_connote" style="height:32px;" class="button" value="yes">
				Print Pos Malaysia Consignment
				</button>

			</div>
			<div class="pos-status-spinner-container">
				<span class="pos-status-spinner"></span>
			</div>
<?php
		}
	}

	function pos_status_enqueue()
	{
		global $pagenow, $typenow;
		if ($typenow  !== 'shop_order' || $pagenow !== 'edit.php') return;

		//$base_dir = str_replace(ABSPATH, '/', realpath(__DIR__));
		$base_dir=plugin_dir_url(__FILE__);
		$css_path = $base_dir . '../css/spinner.css';
		wp_enqueue_style('pos-status-spinner-css', $css_path);

		$css_path = $base_dir . '../css/order.css';
		wp_enqueue_style('order-status-css', $css_path);

		$js_path = $base_dir . '../js/get_pos_status.js';
		wp_enqueue_script('pos-status-ajax-script', $js_path, array('jquery'));
		wp_localize_script('pos-status-ajax-script', 'ajax_var', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('ajax-nonce')
		));
	}
	function generate_connote($post_type=''){
		$post_type=$_GET['post_type'];
		if(isset($_GET['gen_connote'])&&$post_type='shop_order'){
				
			$post_ids = $_GET['post'];
			$result = $this->get_connote_list($post_ids, $post_type);
			wp_redirect($result);
			
		}
	}

	function get_connote_list($post_ids, $post_type){
		$skip_ids = [];
		$process_ids = [];
		$value='PICK UP SCHEDULED';
		// Checking which ids to skip due to missing data and which to process
		foreach ($post_ids as $id) {
			$plconnote = get_post_meta($id, 'plconnote', true);
			$plpreacceptanceid = get_post_meta($id, 'plpreacceptanceid', true);
			$status=get_post_meta($id, 'plprocess', true);
			
			

			if (empty($plconnote) || empty($plpreacceptanceid))
				$skip_ids[] = $id;
			else 
				$process_ids[] = $id;

			if (count($process_ids) >= $this->limit)
				break;
		}
		if (count($skip_ids) > 0){
			$redirect_to = add_query_arg(array(
				'post_type' => $post_type,
				'acti'	=> 'err',
				'msg'	=> 'Please create consignment to proceed.'
			), $redirect_to);
			return $redirect_to;
		}
		if (count($process_ids) > 0) {
			$result = $this->posmalaysia_helper->process_print($process_ids);		
			$error = $result['error'];
			$data = $result['data'];
			$message = $result['message'];
			
			$connote_nos = null;
			if ($data) {
				$connote_nos = $data['connotes'];
			}
			if (!$error && $connote_nos != null && sizeof($connote_nos) > 0) {
				$pdf = $data['pdf'];
				if (!filter_var($pdf, FILTER_VALIDATE_URL)) {
					$redirect_to = add_query_arg(array(
						'post_type' => $post_type,
						'acti'	=> 'err',
						'msg'	=> $pdf
					), $redirect_to);
					return $redirect_to;
				}
			}
			if($error){
				
				$redirect_to = add_query_arg(array(
					
					'post_type' => $post_type,
					'acti'	=> 'err',
					'msg'	=> $error
				), $redirect_to);
				return $redirect_to;
			}else{		
				$redirect_to = add_query_arg(array(
					'post_type' => $post_type,
					'acti'    => 'print',
					'msg'    => $pdf,
					//'paged'=>$_GET['paged']
				), $redirect_to);
				return $redirect_to;				
			}
			
		}
	}

	function get_pos_status()
	{
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['nonce'] ) ) , 'ajax-nonce' ) ){

			wp_die();
		}
		$post_ids = sanitize_text_field($_POST['post_ids']);
		$source = sanitize_text_field($_POST['source']);

		$result = $this->update_pos_status_meta_field($post_ids, $source);
		if (($result[0] == 'Success')) 
			wp_send_json_success($result);
		else 
			wp_send_json_error($result);
		wp_die();
	}

	function update_pos_status_meta_field($post_ids, $source)
	{
		update_option('pos_last_triggered_action', 'get_pos_status');
		if ($source == 'button') {
			if (is_array($post_ids) && count($post_ids) > $this->limit) return array('Error', "Kindly select up to $this->limit orders only");
			if (!is_array($post_ids) || count($post_ids) < 1) return array('Error', 'Kindly select at least 1 order to proceed');
			return $this->_update_pos_status_meta_field($post_ids, $source);
		} elseif ($source == 'onload') {
			if (!is_array($post_ids) || count($post_ids) < 1) return array('Skip', '');
			return $this->_update_pos_status_meta_field($post_ids, $source);
		}
	}

	function _update_pos_status_meta_field($post_ids, $source)
	{
		$skip_ids = [];
		$process_ids = [];

		// Checking which ids to skip due to missing data and which to process
		foreach ($post_ids as $id) {
			$plconnote = get_post_meta($id, 'plconnote', true);
			$plpreacceptanceid = get_post_meta($id, 'plpreacceptanceid', true);

			if (empty($plconnote) || empty($plpreacceptanceid))
				$skip_ids[] = $id;
			else
				$process_ids[] = $id;

			if (count($process_ids) >= $this->limit)
				break;
		}

		$skip = implode(', ', $skip_ids);
		$warnMsg = "No status available for: $skip";
		$errMsg = 'Failed to get the status for selected order(s)';

		if (count($process_ids) > 0) {
			$response = $this->posmalaysia_helper->get_orders_status($process_ids);
			$response_code = $response['responseCode'];
			$response_data = $response['data'];
		} else {
			return $source == 'button' ? array('Error', $warnMsg) : array('Error', $warnMsg);
		}

		$updated_ids = [];
		if (!empty($response_code) && $response_code == 200) {
			foreach ($process_ids as $id) {

				// update order status
				$plconnote = get_post_meta($id, 'plconnote', true);
				$pl_status = esc_html($response_data[$plconnote]);
				update_post_meta($id, 'pl_status',$pl_status);
				// update order status date
				$date = date("j M Y \a\\t g:i A");
				update_post_meta($id, 'pl_status_date', $date);

				$updated_ids[$id] = esc_html(get_post_meta($id, 'pl_status', true));
			}

			$successMsg = count($process_ids) ? 'Status Updated. ' : '';

			if (count($skip_ids) > 0)
				return $source == 'button' ? array('Error', $successMsg . $warnMsg, $updated_ids) : array('Error', $successMsg . $warnMsg, $updated_ids);
			else
				return array('Success', $successMsg, $updated_ids);
		} else
			return $source == 'button' ? array('Error', $errMsg) : array('Error', $errMsg . ' Response code: ' . $response_code);
	}

	function register_pos_custom_statuses() {
		$customstatuses=$this->pos_custom_statuses();
		foreach($customstatuses as $slug=>$label){
			register_post_status( $slug, array(
				'label'                     => $label,
				'public'                    => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
				'exclude_from_search'       => false,
				'label_count'               => _n_noop( "$label <span class='count'>(%s)</span>", "$label <span class='count'>(%s)</span>" ),	
			));
		}
	 }
	
	public function add_pos_custom_statuses( $order_statuses ) {
		$customstatuses=$this->pos_custom_statuses();
		$new_statuses=array_merge( $order_statuses, $customstatuses);
		return $new_statuses;
	}
	public function pos_custom_statuses() {
		return array(
			'wc-pending-pickup'=>'Pos Malaysia- Pending Pickup',
			'wc-in-transit'=>'Pos Malaysia- In Transit',
			'wc-out-for-delivery'=>'Pos Malaysia- Out For Delivery',
			'wc-return'=>'Pos Malaysia- Return',
			'wc-return-success'=>'Pos Malaysia- Return Success'
		);
	}
	public function order_status_update(){
		return array(
		'PICK UP SCHEDULED'=>'wc-pending-pickup',
		'PICKED UP'=>'wc-in-transit',
		'DROPPED OFF'=>'wc-in-transit',
		'IN TRANSIT'=>'wc-in-transit',
		'OUT FOR DELIVERY'=>'wc-out-for-delivery',
		'TO COLLECT'=>'wc-completed',
		'DELIVERED'=>'wc-completed',
		'FAILED'=>'wc-cancelled',
		'RETURN TO SENDER'=>'wc-return',
		'RETURN SUCCESS'=>'wc-return-success',
		);
	}
}