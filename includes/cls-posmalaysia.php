<?php  if ( ! defined( 'ABSPATH' ) ) exit; 

class posmalaysia
{

	private static $initiated;

	public static function init()
	{
		if (!isset(self::$initiated)) {
			self::$initiated = new self();
		}
		return self::$initiated;
	}

	public function InitPlugin()
	{
		require_once PosMalaysiaPLUGINDIR . 'includes/cls-posmalaysia-activated.php';
		require_once PosMalaysiaPLUGINDIR . 'includes/api/cls-posmalaysia-account-api.php';
		require_once PosMalaysiaPLUGINDIR . 'admin/cls-posmalaysia-admin.php';
		require_once PosMalaysiaPLUGINDIR . 'admin/cls-posmalaysia-setting.php';
		require_once PosMalaysiaPLUGINDIR . 'admin/cls-posmalaysia-printlabel.php';
		require_once PosMalaysiaPLUGINDIR . 'admin/cls-posmalaysia-preacceptancedropoff.php';
		require_once PosMalaysiaPLUGINDIR . 'admin/cls-posmalaysia-preacceptancepickup.php';
		require_once PosMalaysiaPLUGINDIR . 'admin/cls-posmalaysia-order.php';
		require_once PosMalaysiaPLUGINDIR . 'admin/cls-posmalaysia-woocommerce-api.php';
		require_once PosMalaysiaPLUGINDIR . 'admin/cls-posmalaysia-notification.php';
		require_once PosMalaysiaPLUGINDIR . 'includes/cls-posmalaysia-helper.php';

		require_once PosMalaysiaPLUGINDIR . 'includes/api/cls-posmalaysia-token-api.php';
		require_once PosMalaysiaPLUGINDIR . 'includes/api/cls-posmalaysia-auth-api.php';
		require_once PosMalaysiaPLUGINDIR . 'includes/api/cls-posmalaysia-tracking-api.php';
		require_once PosMalaysiaPLUGINDIR . 'includes/api/cls-posmalaysia-print-api.php';
		require_once PosMalaysiaPLUGINDIR . 'includes/api/cls-posmalaysia-preacceptance-api.php';
		require_once PosMalaysiaPLUGINDIR . 'includes/api/cls-posmalaysia-status-api.php';
		require_once PosMalaysiaPLUGINDIR . 'includes/api/cls-posmalaysia-api-utils.php';
		new posmalaysia_Activated();
		new posmalaysia_Admin();
		new posmalaysia_Settings();
		new posmalaysia_printlabel();
		//new posmalaysia_preacceptancedropoff();
		//new posmalaysia_preacceptancepickup();
		new posmalaysia_Order();
		new posmalaysia_woocommerce_API();
		new posmalaysia_Notification();
	}
}