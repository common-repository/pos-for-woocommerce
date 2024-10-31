<?php  if ( ! defined( 'ABSPATH' ) ) exit; 

class posmalaysia_Admin
{

	public function __construct()
	{

		$this->posmalaysia_helper = new posmalaysia_Helper();
		$this->api_utils = new Api_Utils_PosMalaysia();
		$this->define_hooks();
	}

	public function define_hooks()
	{
		add_action('plugins_loaded', [$this, 'check_woocommerce_activated']);
				add_action('admin_menu', [$this, 'add_menu']);
	}

	/**
	 * Check if Woocommerce installed
	 */
	public function check_woocommerce_activated()
	{
		if (defined('WC_VERSION')) {
			return;
		}

		add_action('admin_notices', [$this, 'notice_woocommerce_required']);
	}

	/**
	 * Admin error notifying user that Woocommerce is required
	 */
	public function notice_woocommerce_required()
	{
?>
		<div class="notice notice-error">
			<p><?php 'Pos Plugin requires WooCommerce to be installed and activated!' ?></p>
		</div>
<?php
	}

		/**
	 * Add menu
	 */
	public function add_menu()
	{

		add_menu_page(
			'',
			'POS Malaysia Registration',
			'manage_options',
			'pos_reg_page',
			array($this, 'pos_main_page'),
		);

		add_menu_page(
			'',
			'POS Malaysia Track&Trace',
			'manage_options',
			'pos_tracking_page',
			array($this, 'pos_track_page'),
		);
	}

	public function pos_main_page()
	{
		$res = "";
		if ($_POST) {
			if (!wp_verify_nonce( $_POST['send_register'], 'pos_reg_page' ) ) {
				$errorMessage = array(
					"error" =>  "Registration error. Please try again later"
				);
				$res = json_encode($errorMessage);
				include 'view/pos_register.php';
				return;
			}
			$email = sanitize_email($_POST['email']);
			if ($email === null || trim($email) === '') {
				$errorMessage = array(
					"error" =>  "Invalid email format"
				);
				$res = json_encode($errorMessage);
				include 'view/pos_register.php';
				return;
			} else if (preg_match( "/[^0-9.-]/", sanitize_text_field($_POST['phoneno']))) {
				$errorMessage = array(
					"error" =>  "Invalid phone format"
				);
				$res = json_encode($errorMessage);
				include 'view/pos_register.php';
				return;
			} else {
				$res = $this->posmalaysia_helper->register($_POST);
			}
		}
		include 'view/pos_register.php';
	}

	public function pos_track_page()
	{
		$res = "";
		if ($_POST) {
			if (!wp_verify_nonce( $_POST['send_tracking'], 'pos_tracking_page' ) ) {
				$res = array(
					"error" =>  "Tracking error. Please try again later"
				);
				include 'view/pos_tracking.php';
				return;
			}
			if (sanitize_text_field($_POST['tracking']) === null || trim(sanitize_text_field($_POST['tracking']) === '')) {
				$res = array(
					"error" =>  "Tracking no. is compulsary"
				);
				include 'view/pos_tracking.php';
				return;
			} 

			$res = $this->posmalaysia_helper->tracking($_POST);
		}
		include 'view/pos_tracking.php';
	}


}