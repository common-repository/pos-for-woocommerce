<?php if ( ! defined( 'ABSPATH' ) ) exit; 

class PosMalaysia_Activated {
	public function __construct(){
		add_action( 'activated_plugin', [$this,'pos_activation_redirect'] );
		
	}

	
	
	public static function pos_activation_redirect( $plugin='' ) {
		//$posmalaysia_Apikey=get_option('posmalaysia_Apikey');
		//$posmalaysia_secret_key=get_option('posmalaysia_secret_key');
		//$posmalaysia_sender_name=get_option('posmalaysia_sender_name');
	
		$PosAccountDetail=get_option('PosAccountDetail');
		$PosAccountNo=$PosAccountDetail['PosAccountNo'];
		if ((empty($PosAccountDetail))||((empty($PosAccountNo)))) {
			$url=admin_url( 'admin.php?page=wc-settings&tab=shipping&section=posmalaysia');
			header( 'location:'.$url ) ;
			exit(0);
			//exit(wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=posmalaysia' ) )) ;
		}
	}

}